self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = data => {
        // you could refresh a notification badge here with postMessage API
        const title = "Axolotsica";

        return self.registration.showNotification(title, {
            body: data.message,
            data: data.url
        });
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }
});

self.addEventListener('notificationclick',  event => {
    event.notification.close();

    const url = event.notification.data;

    // This looks to see if the current is already open and
    // focuses if it is
    event.waitUntil(clients.matchAll({
        type: "window"
    }).then(function(clientList) {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            if (client.url === url && 'focus' in client)
                return client.focus();
        }
        if (clients.openWindow)
            return clients.openWindow(url);
    }));
});