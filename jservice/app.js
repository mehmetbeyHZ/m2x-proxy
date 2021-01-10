var usbDetect = require('usb-detection');
usbDetect.startMonitoring();

usbDetect.on('add', function(device) { console.log('add', device); });
usbDetect.on('remove', function(device) { console.log('remove', device); });
