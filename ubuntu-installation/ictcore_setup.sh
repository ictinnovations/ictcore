#!/bin/bash

# 1. Add ictcore user and group
echo "Adding ictcore user and group..."
groupadd ictcore
useradd -g ictcore ictcore

# 2. Update User and Group in apache2.conf
echo "Updating apache2.conf..."
APACHE_CONF="/etc/apache2/apache2.conf"
if grep -q "User ictcore" $APACHE_CONF && grep -q "Group ictcore" $APACHE_CONF; then
    echo "User and Group are already set to ictcore in apache2.conf."
else
    sed -i 's/^User .*/User ictcore/' $APACHE_CONF
    sed -i 's/^Group .*/Group ictcore/' $APACHE_CONF
    echo "Updated User and Group to ictcore in apache2.conf."
fi

# 3. Change ownership of /usr/ictcore and /usr/ictfax directories
echo "Changing ownership of /usr/ictcore and /usr/ictfax to ictcore user and group..."
chown -R ictcore:ictcore /usr/ictcore
chown -R ictcore:ictcore /usr/ictfax
echo "Ownership updated."

# 4. Create symbolic links for FreeSWITCH configuration
echo "Linking FreeSWITCH configuration files..."
ln -sf /usr/ictcore/etc/freeswitch/dialplan/ictcore.xml /etc/freeswitch/dialplan/ictcore.xml
ln -sf /usr/ictcore/etc/freeswitch/sip_profiles/ictcore.xml /etc/freeswitch/sip_profiles/ictcore.xml
ln -sf /usr/ictcore/etc/freeswitch/directory/ictcore.xml /etc/freeswitch/directory/ictcore.xml
echo "Symbolic links created."

# 5. Run ICTCore keygen script
echo "Running ICTCore authentication keygen script..."
bash /usr/ictcore/bin/keygen

# Restart Apache to apply changes
echo "Restarting Apache server..."
systemctl restart apache2

echo "Setup completed."
