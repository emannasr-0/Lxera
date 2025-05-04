<!DOCTYPE html>
<html>
<head>
    <title>{{ $pageTitle }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            width: 100% ;
            height: 100%;
            box-sizing: border-box;
            object-fit: cover;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
        }
        img {
            max-width: 100%;
            width: 100%;
            height:100%;
            max-height: 100%; /* Adjust this if needed */
            /* object-fit: cover; */
            
        }
    </style>
</head>
<body>
    <div class="container">
        <div>
            
            <img src="{{ $dynamicImage }}" alt="Dynamic Certificate Image">
            <!-- Other content using $body variables -->
          
        </div>
    </div>
</body>
</html>
