#!/bin/bash



# Capture Errors
OwnError()
{
        #echo $@ >&2
        clear
        echo -e "[ $0 ][ `date` ] \033[31m $@ \e[0m" 
        exit 100 
}


# Checking Permissions
Permission=$(id -u)
if [ $Permission -ne 0 ] 
then
	echo
        echo -e "\033[31m Root Privilege Required... \e[0m"
	echo -e "\033[31m Uses:  sudo $0 {git-username} {php-username} \e[0m"
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
uname -a | grep Ubuntu &> /dev/null
if [ $? -eq 0 ]
then
	echo
	echo -e "\033[34m Ubuntu Detected... \e[0m"
else
	echo
	echo -e "\033[31m Currently this script support only ubuntu distro  :( \e[0m"
	exit 200
fi


# Checking Installed Packages
dpkg --list | grep openssh-server &> /dev/null
OPENSSH=$(echo $?)
dpkg --list | grep git-core &> /dev/null
GITCORE=$(echo $?)
#echo $GITCORE $OPENSSH

# Install Open SSH & Git Core If It Not Installed
if [ $OPENSSH -ne 0 ] || [ $GITCORE -ne 0 ]
then
	# Update Cache
	echo
	echo -e "\033[34m Updating APT Cache... \e[0m"
	sudo apt-get update &> /dev/null || OwnError "Unable To Update APT Cache :("

	# Install Open SSH Server And Git
	echo
	echo -e "\033[34m Installing Open SSH Server and Git... \e[0m"
	sudo apt-get install openssh-server git-core &> /dev/null|| OwnError "Unable To Install Open SSH Server and Git :("
fi



# Check Git User is Already Exist
#clear

if [ $# -lt 1 ]
then
	echo
	echo -e "\033[34m A user account will be created for gitolite setup... \e[0m"
	read -p "Enter the username [git]: " GITUSER

	if [[ $GITUSER = "" ]]
	then
		GITUSER=git
	fi
else
	GITUSER=$1
fi

grep ^$GITUSER /etc/passwd &> /dev/null
if [ $? -eq 0 ]
then
	echo
	echo -e "\033[31m The $GITUSER user is already exist !! \e[0m"
	echo -e "\033[31m Please remove the $GITUSER user or select different username !! \e[0m"
	exit 100
fi


# Create Git User
echo
echo -e "\033[34m Creating System User [$GITUSER]...  \e[0m"
sudo adduser --system --home /home/$GITUSER --shell /bin/bash --group --disabled-login --disabled-password --gecos 'git version control' $GITUSER &> /dev/null || OwnError " Unable to create $GITUSER :("

# Create a bin Directory For Git User
echo
echo -e "\033[34m Creating bin Directory...  \e[0m"
sudo -H -u $GITUSER mkdir /home/$GITUSER/bin || OwnError " Unable to create bin directory :("

# Create a setup Directory For Gitolite Repository
echo
echo -e "\033[34m Creating setup Directory  \e[0m"
sudo -H -u $GITUSER mkdir /home/$GITUSER/setup || OwnError " Unable to create setup directory :("

cd /home/$GITUSER/setup || OwnError " Unable to change directory :("

echo
echo -e "\033[34m Cloning Gitolite...  \e[0m"
sudo -H -u $GITUSER git clone git://github.com/sitaramc/gitolite &> /dev/null || OwnError " Unable to clone gitolote repository :("

# Create a Symbolic Link For Gitolite in /home/git/bin Directory
#sudo -H -u $GITUSER PATH=/home/$GITUSER/bin:$PATH || OwnError " Unable to updat PATH:("
sudo -H -u $GITUSER gitolite/install -to /home/$GITUSER/bin || OwnError " Unable to create symbolic link :("




# Add Web User to Git User Group
#clear
if [ $# -lt 2 ]
then
	echo
	echo -e "\033[34m The php username is described in phpinfo file  \e[0m"
	read -p "Enter the php username [www-data]:  " WEBUSER

	if [[ $WEBUSER = "" ]]
	then
		WEBUSER=www-data
	fi
else
	WEBUSER=$2
fi


# Add Web User to Git Group
echo
echo -e "\033[34m Adding $WEBUSER to $GITUSER Group...  \e[0m"
sudo adduser $WEBUSER $GITUSER &> /dev/null

# Get The Web User Home Dir Path
WEBUSERHOME=$(grep $WEBUSER /etc/passwd | cut -d':' -f6)
if [ -z $WEBUSERHOME ]
then
	echo
	echo -e "\033[31m Unable to Detect $WEBUSER Home Dir !! \e[0m"
	read -p "Enter the home dir path for $WEBUSER: " WEBUSERHOME
fi

# Checks Weather id_rsa Key Exist
sudo ls  $WEBUSERHOME/.ssh/id_rsa &> /dev/null
if [ $? -eq 0 ]
then
	echo
	echo -e "\033[31m 		Found $WEBUSERHOME/.ssh/id_rsa !! \e[0m"
	echo -e "\033[31m 		Moved $WEBUSERHOME/.ssh/id_rsa to $WEBUSERHOME/.ssh/id_rsa.bak !! \e[0m"
	sudo mv $WEBUSERHOME/.ssh/id_rsa $WEBUSERHOME/.ssh/id_rsa.bak
	sudo mv $WEBUSERHOME/.ssh/id_rsa.pub $WEBUSERHOME/.ssh/id_rsa.pub.bak
fi


# Generate SSH Keys For Web User
echo
echo -e "\033[34m Generating SSH Keys For $WEBUSER \e[0m"
sudo -H -u $WEBUSER ssh-keygen -q -N '' -f $WEBUSERHOME/.ssh/id_rsa || OwnError " Unable to create symbolic link :("
sudo cp $WEBUSERHOME/.ssh/id_rsa.pub /home/$GITUSER/$WEBUSER.pub || OwnError " Unable to copy $WEBUSER Pubkey :(" 
sudo chown $GITUSER:$GITUSER /home/$GITUSER/$WEBUSER.pub || OwnError " Unable to change ownership of $WEBUSER :("

# Setup Gitolite Admin
echo
echo -e "\033[34m Setup Gitolite Admin...  \e[0m"
cd /home/$GITUSER
sudo -H -u $GITUSER /home/$GITUSER/bin/gitolite setup -pk $WEBUSER.pub &> /dev/null || OwnError " Unable to setup Gitolite Admin (Key) :("

# Change UMASK Value
echo
echo -e "\033[34m Changing UMASK Value...  \e[0m"
sudo -H -u $GITUSER sed -i 's/0077/0007/g' /home/$GITUSER/.gitolite.rc || OwnError " Unable to change UMASK :("

# Success Message
echo
echo
echo -e "\033[34m Gitolite Admin is ready to work...  \e[0m"

# Verify Gitolite Admin is Cloned
#sudo -H -u $WEBUSER $GITUSER@localhost:gitolite-admin.git /tmp/gitolite-admin || OwnError " Unable to Clone Gitolite Admin :("
#sudo rm -rf /tmp/gitolite-admin
