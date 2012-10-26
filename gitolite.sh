#!/bin/bash



# Variables
BASEPATH=$(dirname $0 )
LOGFILE=/var/log/gitolite.sh.log


# Checking Permissions
Permission=$(id -u)
if [ $Permission -ne 0 ] 
then
        echo
        echo -e "\033[31m Root Privilege Required... \e[0m" #| tee -ai $LOGFILE
        echo -e "\033[31m Uses: sudo bash $0 [git-username] [php-username] \e[0m" #| tee -ai $LOGFILE
        exit 100
fi

# Capture Errors
OwnError()
{
        echo | tee -ai $LOGFILE
        echo -e "[ $0 ][ `date` ] \033[31m $@ \e[0m" | tee -ai $LOGFILE
        exit 101 
}

# Makes Log File Easy To Read
echo &>> $LOGFILE
echo &>> $LOGFILE
echo &>> $LOGFILE
echo -e "\033[34m Gitolite Admin is setup started at `date` \e[0m" | tee -ai $LOGFILE
# Detect Linux Distro

# Detection Of Ubuntu
uname -a | grep Ubuntu &>> $LOGFILE
if [ $? -eq 0 ]
then
        echo | tee -ai $LOGFILE
        echo -e "\033[34m Ubuntu Detected... \e[0m" | tee -ai $LOGFILE

	# Checking Installed Packages
	dpkg --list | grep openssh-server &>> $LOGFILE
	OPENSSH=$(echo $?)
	dpkg --list | grep git-core &>> $LOGFILE
	GITCORE=$(echo $?)
	dpkg --list | grep curl &>> $LOGFILE
	CURL=$(echo $?)
	echo Checking Installed Packages = $GITCORE $OPENSSH $CURL &>> $LOGFILE


	# Install Git, Curl & Open SSH If It Not Installed
	if [ $OPENSSH -ne 0 ] || [ $GITCORE -ne 0 ] || [ $CURL -ne 0 ]
	then
		# Update Cache
		echo -e "\033[34m Updating apt cache... \e[0m" | tee -ai $LOGFILE
		sudo apt-get update || OwnError "Unable To Update APT Cache"

		# Install Open SSH Server And Git
		echo -e "\033[34m Installing openssh-server, git-core or curl... \e[0m"
		sudo apt-get -y install openssh-server git-core curl &>> $LOGFILE \
		|| OwnError "Unable To Install Open SSH Server and Git"
	fi

else
        echo | tee -ai $LOGFILE
       	echo -e "\033[31m Currently this script support only ubuntu distro \e[0m"
       	exit 200
fi





# Ask User If Script Run Withour ARGS
if [ $# -lt 1 ]
then
	echo -e "\033[34m A user account will be created for gitolite setup... \e[0m" \
	| tee -ai $LOGFILE
	read -p "Enter the username [git]: " GITUSER

# Enter Then Used Default Git
if [[ $GITUSER = "" ]]
then
	GITUSER=git 
	echo GITUSER = $GITUSER &>> $LOGFILE
fi      
else            
	GITUSER=$1
	echo GITUSER = $GITUSER &>> $LOGFILE
fi

# Check Passwd File For Exsiting User        
grep ^$GITUSER: /etc/passwd &>> $LOGFILE
if [ $? -eq 0 ]
then
	echo -e "\033[31m The $GITUSER user is already exist !! \e[0m" | tee -ai $LOGFILE
	echo -e "\033[31m Please select the different username !! \e[0m" | tee -ai $LOGFILE
        exit 102
fi

# Create Git User
echo -e "\033[34m Creating System User [$GITUSER]  \e[0m" | tee -ai $LOGFILE
sudo adduser --system --home /home/$GITUSER --shell /bin/bash --group \
--disabled-login --disabled-password --gecos 'git version control' $GITUSER &>> $LOGFILE \
|| OwnError "Unable to create $GITUSER"

# Copy Skeleton Contents
echo -e "\033[34m Copying system files...  \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER cp /etc/skel/.profile /etc/skel/.bashrc /etc/skel/.bash_logout /home/$GITUSER/

# Create a bin Directory For Git User
echo -e "\033[34m Creating bin directory \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER mkdir /home/$GITUSER/bin || OwnError "Unable to create bin directory"




# Create a setup Directory For Gitolite Repository
echo -e "\033[34m Creating setup directory \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER mkdir /home/$GITUSER/setup \
|| OwnError "Unable to create setup directory"

cd /home/$GITUSER/setup || OwnError " Unable to change directory"

echo | tee -ai $LOGFILE
echo -e "\033[34m Cloning Gitolite Server Repository... \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER git clone git://github.com/sitaramc/gitolite  &>> $LOGFILE \
|| OwnError "Unable to clone gitolote repository"

# Create a Symbolic Link For Gitolite in /home/git/bin Directory
echo -e "\033[34m Creating Gitolite symbolic link  \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER gitolite/install -to /home/$GITUSER/bin \
|| OwnError "Unable to create symbolic link for Gitolite"




# Ask User If Script Run Withour ARGS
if [ $# -lt 2 ]
then
	echo -e "\033[34m PHP username is given at Gitolite Admin [Need Help section] \e[0m" \
	| tee -ai $LOGFILE
	read -p " Enter the php username [www-data]:  " WEBUSER

	if [[ $WEBUSER = "" ]]
	then
		WEBUSER=www-data
		echo WEBUSER = $WEBUSER &>> $LOGFILE
	fi
else
	WEBUSER=$2
	echo WEBUSER = $WEBUSER &>> $LOGFILE
fi


# Add Web User to Git Group
echo -e "\033[34m Adding $WEBUSER to $GITUSER group  \e[0m" | tee -ai $LOGFILE
sudo adduser $WEBUSER $GITUSER &>> $LOGFILE

# Get The Web User Home Dir Path
WEBUSERHOME=$(grep $WEBUSER /etc/passwd | cut -d':' -f6)
echo WEBUSERHOME = $WEBUSERHOME &>> $LOGFILE
if [ -z $WEBUSERHOME ]
then
	echo | tee -ai $LOGFILE
	echo -e "\033[31m Unable to detect $WEBUSER home dir !! \e[0m" | tee -ai $LOGFILE
	read -p "Enter the home dir path for $WEBUSER: " WEBUSERHOME
fi

# Checks .ssh Directory Exist
ls $WEBUSERHOME/.ssh &>> tee -ai $LOGFILE
if [ $? -ne 0 ]
then
	echo -e "\033[34m Creating .ssh directory \e[0m" | tee -ai $LOGFILE
	sudo mkdir $WEBUSERHOME/.ssh || OwnError "Unable to crate $WEBUSERHOME/.ssh"
	sudo chown -R $WEBUSER:$WEBUSER $WEBUSERHOME/.ssh || OwnError "Unable to chown .ssh"
fi

# Checks Weather id_rsa Key Exist
sudo ls  $WEBUSERHOME/.ssh/id_rsa &>> $LOGFILE
if [ $? -eq 0 ]
then
	echo -e "\033[34m The ssh key id_rsa already exist \e[0m" | tee -ai $LOGFILE
else

	# Generate SSH Keys For Web User
	echo -e "\033[34m Generating ssh keys for $WEBUSER \e[0m" | tee -ai $LOGFILE
	sudo -H -u $WEBUSER ssh-keygen -q -N '' -f $WEBUSERHOME/.ssh/id_rsa \
	|| OwnError "Unable to create ssh keys for $WEBUSER"
fi


# Create known_hosts file if not exist
# Or if known_hosts exist update timestamp 
sudo touch $WEBUSERHOME/.ssh/known_hosts || OwnError "Unable to create known_hosts"

# Give 666 Permission To Add SSH Server Fingerprint
sudo chmod 666 $WEBUSERHOME/.ssh/known_hosts || OwnError "Unable to chmod 666 known_hosts"

# Use Wildcard For Match All The Domains
sudo echo -n "* " >> $WEBUSERHOME/.ssh/known_hosts \
|| OwnError "Unable to add wildcard as servername"

# Copy The SSH Server Fingerprint
cat /etc/ssh/ssh_host_rsa_key.pub >> $WEBUSERHOME/.ssh/known_hosts \
|| OwnError "Unable to add ssh server fingerprint"


# Give Back 644 Permission To Add SSH Server Fingerprint
sudo chmod 644 $WEBUSERHOME/.ssh/known_hosts || OwnError "Unable to chmod 644 known_hosts"
sudo chown $WEBUSER:$WEBUSER $WEBUSERHOME/.ssh/known_hosts \
|| OwnError "Unable to chown known_hosts"


# Setup Gitolite Admin
echo | tee -ai $LOGFILE
echo -e "\033[34m Setup Gitolite Admin...  \e[0m" | tee -ai $LOGFILE

sudo cp $WEBUSERHOME/.ssh/id_rsa.pub /home/$GITUSER/$WEBUSER.pub \
|| OwnError "Unable to copy $WEBUSER Pubkey"
	
sudo chown $GITUSER:$GITUSER /home/$GITUSER/$WEBUSER.pub \
|| OwnError "Unable to change ownership of $WEBUSER"

cd /home/$GITUSER

sudo -H -u $GITUSER /home/$GITUSER/bin/gitolite setup -pk $WEBUSER.pub &>> $LOGFILE \
|| OwnError "Unable to setup Gitolite Admin (Key)"

# Change UMASK Value
echo -e "\033[34m Changing umask value  \e[0m" | tee -ai $LOGFILE
sudo -H -u $GITUSER sed -i 's/0077/0007/g' /home/$GITUSER/.gitolite.rc \
|| OwnError "Unable to change UMASK"


# Installing Post Receive Hooks
echo -e "\033[34m Creating post-receive hooks \e[0m" | tee -ai $LOGFILE
cd $BASEPATH

cd ../../../public/ || OwnError "Unable to change directory for hookspath"
if [ -f .hookspath.rt ]
then
	HOOKSPATH=$(cat .hookspath.rt)
       	echo HOOKSPATH = $HOOKSPATH &>> $LOGFILE

	CURLPATH=$(whereis curl | cut -d' ' -f2)

	sudo -H -u $GITUSER echo "$CURLPATH -s -L \"$HOOKSPATH\" > /dev/null " \
	&>> /home/$GITUSER/.gitolite/hooks/common/post-receive

	sudo chmod a+x /home/$GITUSER/.gitolite/hooks/common/post-receive
	sudo chown $GITUSER:$GITUSER /home/$GITUSER/.gitolite/hooks/common/post-receive
	sudo -H -u $GITUSER /home/$GITUSER/bin/gitolite setup --hooks-only
else
	echo | tee -ai $LOGFILE
	echo -e "\033[31m Can't create post-receive hooks...  \e[0m" | tee -ai $LOGFILE
	echo
fi


# Log Messages
echo | tee -ai $LOGFILE
echo -e "\033[34m For detailed installation messages use the following command \e[0m" \
| tee -ai $LOGFILE
echo -e "\033[34m cat $LOGFILE \e[0m" | tee -ai $LOGFILE

echo
echo -e "\033[34m Gitolite Admin is successfully setup at `date` \e[0m" | tee -ai $LOGFILE
echo -e "\033[34m Please go back to Gitolite Admin, test connection and save settings. \e[0m" \
| tee -ai $LOGFILE



