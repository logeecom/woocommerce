setlocal
set WOOCOMMERCE_PLUGIN=C:\projects\channelengine-woocommerce
set WOOCOMMERCE_ROOT=C:\projects\woocommerce

echo %WOOCOMMERCE_PLUGIN%
echo %WOOCOMMERCE_ROOT%

mklink /D "%WOOCOMMERCE_ROOT%\wp-content\plugins\channelengine" "%WOOCOMMERCE_PLUGIN%"