<!DOCTYPE html>
<html>
<head>
    <title>Interactive Clickjacking Example</title>
    <style>
        #fakeButton {
            position: absolute;
            top: 50px;
            left: 50px;
            z-index: 1;
        }

        #hiddenButton {
            opacity: 0.5; /* Set to 0 in an actual attack to hide it */
            position: absolute;
            z-index: 2; /* Ensures it's above the fake button */
        }
    </style>
</head>
<body>
    <div id="fakeButton">Click Me!</div>
    <button id="hiddenButton" onclick="alert('Clickjacked!')">Hidden Button</button>

    <script>
        document.addEventListener('mousemove', function(e) {
            // Move the hidden button under the cursor right before the click
            var hiddenButton = document.getElementById('hiddenButton');
            hiddenButton.style.top = e.clientY + 'px';
            hiddenButton.style.left = e.clientX + 'px';
        });
    </script>
</body>
</html>