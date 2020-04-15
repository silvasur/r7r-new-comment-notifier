#!/usr/bin/env bash

r7r-plugin-packer \
    --output=new_comment_notifier.rpk \
    --codefile=plugin.php \
    --classname=new_comment_notifier \
    --pluginname=new_comment_notifier \
    --author='Laria Carolin Chabowski <laria@laria.me>' \
    --versiontext="0.2.1" \
    --versioncount=4 \
    --api=5 \
    --shortdesc="new_comment_notifier sends you a mail, if someone has written a comment." \
    --licensefile=COPYING \
    --tpldir=tpls
