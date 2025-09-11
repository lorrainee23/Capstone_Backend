const permissions = window.cordova?.plugins?.permissions;
const bluetoothSerial = window.bluetoothSerial;

const devices = [];
let selectedDevice = localStorage.getItem('printer_selected_device') || "";
let isConnected = false;

export const bluetoothService = {
    devices,
    get selectedDevice() {
        return selectedDevice;
    },
    get isConnected() {
        return isConnected;
    },

    requestPermissions: async () => {
        if (!permissions) return true;

        const perms = [
            permissions.BLUETOOTH_CONNECT,
            permissions.BLUETOOTH_SCAN,
            permissions.ACCESS_FINE_LOCATION,
        ];

        return new Promise((resolve, reject) => {
            permissions.hasPermission(
                perms,
                (status) => {
                    if (status.hasPermission) resolve(true);
                    else {
                        permissions.requestPermissions(
                            perms,
                            (status2) => {
                                if (status2.hasPermission) resolve(true);
                                else reject("Bluetooth permission denied");
                            },
                            reject
                        );
                    }
                },
                reject
            );
        });
    },

    listDevices: async () => {
        await bluetoothService.requestPermissions();
        return new Promise((resolve, reject) => {
            if (!bluetoothSerial)
                return reject("Bluetooth plugin not available");

            bluetoothSerial.list(
                (data) => {
                    devices.splice(0, devices.length, ...data);
                    resolve(data);
                },
                (err) => reject(err)
            );
        });
    },

    connect: async (address) => {
        if (!bluetoothSerial) throw "Bluetooth plugin not available";

        if (selectedDevice === address && isConnected) return;

        selectedDevice = address;
        return new Promise((resolve, reject) => {
            bluetoothSerial.connect(
                address,
                () => {
                    isConnected = true;
                    localStorage.setItem('printer_selected_device', selectedDevice);
                    resolve(true);
                },
                (err) => {
                    selectedDevice = "";
                    isConnected = false;
                    localStorage.removeItem('printer_selected_device');
                    reject(err);
                }
            );
        });
    },

    disconnect: async () => {
        if (!selectedDevice || !bluetoothSerial) return;

        return new Promise((resolve, reject) => {
            bluetoothSerial.disconnect(
                () => {
                    selectedDevice = "";
                    isConnected = false;
                    localStorage.removeItem('printer_selected_device');
                    resolve(true);
                },
                (err) => reject(err)
            );
        });
    },

    checkConnection: async () => {
        if (!selectedDevice || !bluetoothSerial) return false;

        return new Promise((resolve) => {
            bluetoothSerial.isConnected(
                () => {
                    isConnected = true;
                    resolve(true);
                },
                () => {
                    isConnected = false;
                    resolve(false);
                }
            );
        });
    },

    printText: async (text) => {
        if (!isConnected || !selectedDevice) throw "Printer not connected";
        return new Promise((resolve, reject) => {
            bluetoothSerial.write(
                text,
                () => resolve(true),
                (err) => reject(err)
            );
        });
    },
};
