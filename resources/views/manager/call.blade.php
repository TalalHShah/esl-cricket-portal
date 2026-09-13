<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &middot; ESL Cricket Call</title>
    <link rel="icon" type="image/png" href="{{ asset('images/esl-logo.png') }}">
    <style>
        html, body { height: 100%; margin: 0; background: #0D1220; }
        #callRoot { position: fixed; inset: 0; }
    </style>
</head>
<body>
    <div id="callRoot"></div>
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        new JitsiMeetExternalAPI('meet.jit.si', {
            roomName: @json($roomName),
            parentNode: document.getElementById('callRoot'),
            width: '100%',
            height: '100%',
            userInfo: { displayName: @json($displayName) },
            configOverwrite: { prejoinPageEnabled: false },
        });
    </script>
</body>
</html>
