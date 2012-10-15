#!/bin/bash



echo -e "\033[34m Gitolite admin installation started at `date` \e[0m" | tee -ai /var/log/gitolite.sh.log
echo -e "\033[34m For detailed installation messages use the following command \e[0m" 
echo -e "\033[34m tail -f /var/log/gitolite.sh.log \e[0m" 

# Capture Errors
OwnError()
{
        #echo $@ >&2
        #clear
	echo
        echo -e "[ $0 ][ `date` ] \033[31m $@ \e[0m" | tee -ai /var/log/gitolite.sh.log 
        exit 100 
}


# Checking Permissions
Permission=$(id -u)
if [ $Permission -ne 0 ] 
then
	echo
        echo -e "\033[31m Root Privilege Required... \e[0m" | tee -ai /var/log/gitolite.sh.log
	echo -e "\033[31m Uses: sudo bash $0 {git-username} {php-username} \e[0m" | tee -ai /var/log/gitolite.sh.log
        exit 100 
fi

# Checking Arguments
#if [ $# -ne 2 ]
#then
#	echo
#	echo -e "\033[31m Uses:  sudo $0 {git-username} {php-username} \e[0m"
#	echo -e "\033[31m git-username: The git user is created with given name  \e[0m"
#	echo -e "\033[31m php-username:	Use the user described in phpinfo \e[0m"
#	exit 200
#fi


# Detect Linux Distro
uname -a | grep Ubuntu &>> /var/log/gitolite.sh.log
if [ $? -eq 0 ]
then
	echo
	echo -e "\033[34m Ubuntu Detected... \e[0m" | tee -ai /var/log/gitolite.sh.log
else
	echo
	echo -e "\033[31m Currently this script support only ubuntu distro  \e[0m" | tee -ai /var/log/gitolite.sh.log
	exit 200
fi


# Checking Installed Packages
dpkg --list | grep openssh-server &>> /var/log/gitolite.sh.log
OPENSSH=$(echo $?)
dpkg --list | grep git-core &>> /var/log/gitolite.sh.log
GITCORE=$(echo $?)
#echo $GITCORE $OPENSSH

# Install Open SSH & Git Core If It Not Installed
if [ $OPENSSH -ne 0 ] || [ $GITCORE -ne 0 ]
then
	# Update Cache
	#echo
	echo -e "\033[34m Updating apt cache... \e[0m" | tee -ai /var/log/gitolite.sh.log
	sudo apt-get update &>> /var/log/gitolite.sh.log || OwnError "Unable To Update APT Cache"

	# Install Open SSH Server And Git
	#echo
	echo -e "\033[34m Installing openssh-server and git-core... \e[0m" | tee -ai /var/log/gitolite.sh.log
	sudo apt-get -y install openssh-server git-core &>> /var/log/gitolite.sh.log || OwnError "Unable To Install Open SSH Server and Git"
fi


# Check Git User is Already Exist
#clear
if [ $# -lt 1 ]
then
	#echo
	echo -e "\033[34m A user account will be created for gitolite setup... \e[0m" | tee -ai /var/log/gitolite.sh.log
	read -p "Enter the username [git]: " GITUSER

	if [[ $GITUSER = "" ]]
	then
		GITUSER=git
		echo GITUSER = $GITUSER &>> /var/log/gitolite.sh.log
	fi
else
	GITUSER=$1
	echo GITUSER = $GITUSER &>> /var/log/gitolite.sh.log
fi

grep ^$GITUSER$ /etc/passwd &>> /var/log/gitolite.sh.log
if [ $? -eq 0 ]
then
	#echo
	echo -e "\033[31m The $GITUSER user is already exist !! \e[0m" | tee -ai /var/log/gitolite.sh.log
	echo -e "\033[31m Please remove the $GITUSER user or select different username !! \e[0m" | tee -ai /var/log/gitolite.sh.log
	exit 100
fi


# Create Git User
#echo
echo -e "\033[34m Creating System User [$GITUSER]...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo adduser --system --home /home/$GITUSER --shell /bin/bash --group --disabled-login --disabled-password --gecos 'git version control' $GITUSER &>> /var/log/gitolite.sh.log || OwnError "Unable to create $GITUSER"

# Create a bin Directory For Git User
#echo
echo -e "\033[34m Creating bin directory...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo -H -u $GITUSER mkdir /home/$GITUSER/bin || OwnError "Unable to create bin directory"

# Create a setup Directory For Gitolite Repository
#echo
echo -e "\033[34m Creating setup directory  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo -H -u $GITUSER mkdir /home/$GITUSER/setup || OwnError "Unable to create setup directory"

cd /home/$GITUSER/setup || OwnError " Unable to change directory"

#echo
echo -e "\033[34m Cloning Gitolite...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo -H -u $GITUSER git clone git://github.com/sitaramc/gitolite &>> /var/log/gitolite.sh.log || OwnError "Unable to clone gitolote repository"

# Create a Symbolic Link For Gitolite in /home/git/bin Directory
#sudo -H -u $GITUSER PATH=/home/$GITUSER/bin:$PATH || OwnError " Unable to updat PATH:("
#echo
echo -e "\033[34m Creating Gitolite symbolic link...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo -H -u $GITUSER gitolite/install -to /home/$GITUSER/bin || OwnError "Unable to create symbolic link for Gitolite"




# Add Web User to Git User Group
#clear
if [ $# -lt 2 ]
then
	#echo
	echo -e "\033[34m The PHP username is given at Gitolite Admin [Need Help section]  \e[0m" | tee -ai /var/log/gitolite.sh.log
	read -p "Enter the php username [www-data]:  " WEBUSER

	if [[ $WEBUSER = "" ]]
	then
		WEBUSER=www-data
		echo WEBUSER = $WEBUSER &>> /var/log/gitolite.sh.log
	fi
else
	WEBUSER=$2
	echo WEBUSER = $WEBUSER &>> /var/log/gitolite.sh.log
fi


# Add Web User to Git Group
#echo
echo -e "\033[34m Adding $WEBUSER to $GITUSER group...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo adduser $WEBUSER $GITUSER &>> /var/log/gitolite.sh.log

# Get The Web User Home Dir Path
WEBUSERHOME=$(grep $WEBUSER /etc/passwd | cut -d':' -f6)
if [ -z $WEBUSERHOME ]
then
	echo
	echo -e "\033[31m Unable to detect $WEBUSER home dir !! \e[0m" | tee -ai /var/log/gitolite.sh.log
	read -p "Enter the home dir path for $WEBUSER: " WEBUSERHOME
fi

# Checks Weather rt_rsa Key Exist
sudo ls  $WEBUSERHOME/.ssh/rt_rsa &>> /var/log/gitolite.sh.log
if [ $? -eq 0 ]
then
	echo -e "\033[34m The ssh key rt_rsa already exist... \e[0m"
else
	# Generate SSH Keys For Web User
	#echo
	echo -e "\033[34m Generating ssh keys for $WEBUSER \e[0m" | tee -ai /var/log/gitolite.sh.log
	sudo -H -u $WEBUSER ssh-keygen -q -N '' -f $WEBUSERHOME/.ssh/rt_rsa || OwnError "Unable to create ssh keys for $WEBUSER"
	sudo cp $WEBUSERHOME/.ssh/rt_rsa.pub /home/$GITUSER/$WEBUSER.pub || OwnError "Unable to copy $WEBUSER Pubkey" 
	sudo chown $GITUSER:$GITUSER /home/$GITUSER/$WEBUSER.pub || OwnError "Unable to change ownership of $WEBUSER"
fi



# Setup Gitolite Admin
echo
echo -e "\033[34m Setup Gitolite Admin...  \e[0m" | tee -ai /var/log/gitolite.sh.log
cd /home/$GITUSER
sudo -H -u $GITUSER /home/$GITUSER/bin/gitolite setup -pk $WEBUSER.pub &>> /var/log/gitolite.sh.log || OwnError "Unable to setup Gitolite Admin (Key)"

# Change UMASK Value
#echo
echo -e "\033[34m Changing umask value...  \e[0m" | tee -ai /var/log/gitolite.sh.log
sudo -H -u $GITUSER sed -i 's/0077/0007/g' /home/$GITUSER/.gitolite.rc || OwnError "Unable to change UMASK"

# Success Message
#echo
echo
echo -e "\033[34m Gitolite Admin is successfully setup at `date` \e[0m" | tee -ai /var/log/gitolite.sh.log
echo -e "\033[34m Please go back to Gitolite Admin, test connection and save settings. \e[0m" | tee -ai /var/log/gitolite.sh.log


# Verify Gitolite Admin is Cloned
#sudo -H -u $WEBUSER $GITUSER@localhost:gitolite-admin.git /tmp/gitolite-admin || OwnError " Unable to Clone Gitolite Admin"
#sudo rm -rf /tmp/gitolite-admin
