#!/usr/bin/env bash

WC_VERSION="5.4.0"

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_PLUG_DIR=$TMPDIR/wordpress/wp-content/plugins/

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

set -ex

install_wp() {

	if [ ! -d $WP_PLUG_DIR ]; then
		return;
	fi

    download https://downloads.wordpress.org/plugin/woocommerce.${WC_VERSION}.zip $TMPDIR/woocommerce.${WC_VERSION}.zip
    unzip -q $TMPDIR/woocommerce.${WC_VERSION}.zip -d $WP_PLUG_DIR
    
}

install_wp