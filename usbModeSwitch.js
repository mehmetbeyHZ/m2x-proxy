const usbDetect = require('usb-detection');


const { exec} = require('child_process');
const child = exec('ls');


usbDetect.startMonitoring();

usbDetect.on('add', function(device) {
    if (device.vendorId === 7099 && device.productId === 61440)
    {
        console.log("Attach.")
        execShellCommand("sudo usb_modeswitch -v 1bbb -p f000 -c /etc/usb_modeswitch.d/alcatel.conf")
        execShellCommand("sudo ip route add default via 192.168.3.1 dev wlo1 ")
    }
});
function execShellCommand(cmd) {
    const exec = require("child_process").exec;
    return new Promise((resolve, reject) => {
        exec(cmd, { maxBuffer: 1024 * 500 }, (error, stdout, stderr) => {
            if (error) {
                console.warn(error);
            } else if (stdout) {
                console.log(stdout);
            } else {
                console.log(stderr);
            }
            resolve(stdout ? true : false);
        });
    });
}