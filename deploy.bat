@echo off
echo clean ws
rmdir /S /Q ws
mkdir ws
echo copy source
cp -r admin ws/
cp -r api ws/
cp -r app ws/
cp -r data ws/
cp -r eccore ws/
cp -r external ws/
cp -r history ws/
cp -r includes ws/
cp -r languages ws/
cp -r themes ws/
cp -r weixin ws/
cp common.php ws/
cp index.php ws/
cp favicon.ico ws/
rm -f ws/data/config.inc.php
rm -rf ws/themes/store/default/styles/style1
rm -rf ws/themes/store/default/styles/style2
rm -rf ws/themes/store/default/styles/style3
rm -rf ws/themes/store/default/styles/style4
rm -rf ws/themes/store/default/styles/style5
rm -rf ws/themes/store/default/styles/style6
rm -rf ws/themes/store/default/styles/style7
rm -rf ws/themes/store/default/styles/style8
echo delpy...
pscp -r -pw "%DAJIKE_TEST%" ws/* root@180.166.105.167:/app/data/site/dajike.com/test.dajike.com