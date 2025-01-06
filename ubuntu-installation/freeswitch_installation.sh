#!/bin/bash

# Script to install FreeSWITCH on Ubuntu 24.04 LTS
# Ensure the script is run as root or with sudo privileges

echo "Updating and upgrading system packages..."
sudo apt update && sudo apt upgrade -y

echo "Installing prerequisite packages..."
sudo apt install --yes build-essential pkg-config uuid-dev zlib1g-dev libjpeg-dev libsqlite3-dev libcurl4-openssl-dev \
    libpcre3-dev libspeexdsp-dev libldns-dev libedit-dev libtiff5-dev yasm libopus-dev libsndfile1-dev unzip \
    libspeex-dev libavformat-dev libswscale-dev liblua5.2-dev liblua5.2-0 cmake libpq-dev \
    unixodbc-dev autoconf automake ntpdate libxml2-dev libpq-dev libpq5 sngrep git wget

# Install libks
echo "Installing libks library..."
sudo git clone https://github.com/signalwire/libks.git /usr/local/src/libks
cd /usr/local/src/libks
sudo cmake .
sudo make && sudo make install
sudo sh -c 'ldconfig && ldconfig -p' | grep libks

# Install Sofia-Sip
echo "Installing Sofia-Sip library..."
sudo git clone https://github.com/freeswitch/sofia-sip /usr/local/src/sofia-sip
cd /usr/local/src/sofia-sip
sudo ./bootstrap.sh
sudo ./configure
sudo make && sudo make install
sudo sh -c 'ldconfig && ldconfig -p' | grep sofia

# Install SpanDSP
echo "Installing SpanDSP library..."
sudo git clone https://github.com/freeswitch/spandsp /usr/local/src/spandsp
cd /usr/local/src/spandsp
sudo git reset --hard 67d2455efe02e7ff0d897f3fd5636fed4d54549e
sudo ./bootstrap.sh
sudo ./configure
sudo make && sudo make install
sudo sh -c 'ldconfig && ldconfig -p' | grep spandsp

# Install FreeSWITCH
echo "Installing FreeSWITCH..."
sudo wget -c https://files.freeswitch.org/releases/freeswitch/freeswitch-1.10.11.-release.tar.xz -P /usr/local/src
cd /usr/local/src
sudo tar -Jxvf freeswitch-1.10.11.-release.tar.xz
cd freeswitch-1.10.11.-release

echo "Updating modules.conf..."
sudo sed -i 's/applications\/mod_signalwire/#applications\/mod_signalwire/' modules.conf
sudo sed -i 's/applications\/mod_av/#applications\/mod_av/' modules.conf

echo "Running configure script..."
sudo ./configure --enable-core-odbc-support --enable-core-pgsql-support
sudo make
sudo make install
sudo make cd-sounds-install
sudo make cd-moh-install

# Post-install setup
echo "Creating symlinks..."
sudo ln -s /usr/local/freeswitch/conf /etc/freeswitch 
sudo ln -s /usr/local/freeswitch/bin/fs_cli /usr/bin/fs_cli 
sudo ln -s /usr/local/freeswitch/bin/freeswitch /usr/sbin/freeswitch

echo "Creating unprivileged user..."
sudo groupadd freeswitch
sudo adduser --quiet --system --home /usr/local/freeswitch --gecos 'FreeSWITCH open source softswitch' --ingroup freeswitch freeswitch --disabled-password
sudo chown -R freeswitch:freeswitch /usr/local/freeswitch/
sudo chmod -R ug=rwX,o= /usr/local/freeswitch/
sudo chmod -R u=rwx,g=rx /usr/local/freeswitch/bin/*

# Systemd service setup
echo "Setting up FreeSWITCH as a systemd service..."
sudo bash -c 'cat > /etc/systemd/system/freesetich.service <<EOF
[Unit]
Description=FreeSWITCH open source softswitch
Wants=network-online.target
Requires=network.target local-fs.target
After=network.target network-online.target local-fs.target

[Service]
Type=forking
PIDFile=/usr/local/freeswitch/run/freeswitch.pid
Environment="DAEMON_OPTS=-nonat"
Environment="USER=freeswitch"
Environment="GROUP=freeswitch"
EnvironmentFile=-/etc/default/freeswitch
ExecStartPre=/bin/chown -R \${USER}:\${GROUP} /usr/local/freeswitch
ExecStart=/usr/local/freeswitch/bin/freeswitch -u \${USER} -g \${GROUP} -ncwait \${DAEMON_OPTS}
TimeoutSec=45s
Restart=always

[Install]
WantedBy=multi-user.target
EOF'

echo "Reloading systemd daemon..."
sudo systemctl daemon-reload
sudo systemctl start freesetich
sudo systemctl status freesetich
sudo systemctl enable freesetich

echo "FreeSWITCH installation completed successfully!"

