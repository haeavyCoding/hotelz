<!DOCTYPE html>
<html>
<?php
    $id = $_GET['id'];
    include_once('../config.php');
    $sql = "select * from hotels where id =  $id";
    $connect = mysqli_query($conn, $sql);
    if(mysqli_num_rows($connect)>0){
        $result = mysqli_fetch_assoc($connect);
    }else{
        return handleRedirect();
    }
    ?>
<head>
    <title>Best Hotel in Gomti Nagar||Cheap Hotels in Gomti Nagar||Best Hotel in Lucknow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Best Hotel in Gomti Nagar||Cheap Hotels in Gomti Nagar||Best Hotel in Lucknow" />
    <script type="application/x-javascript">
        addEventListener("load", function() {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
            window.scrollTo(0, 1);
        }

        function trackAction(action) {
            fetch('server/trackVisit.php?action=' + action)
                .then(response => console.log('User action tracked:', action))
                .catch(error => console.error('Error tracking user action:', error));
        }

        function handleRedirect(event) {
            const selectedValue = event.target.value;
            if (selectedValue === "excellent" || selectedValue === "good") {
                trackAction('google');
                window.location.href = "<?php echo $result['google_review_link'] ?>";
            } else if (selectedValue === "neutral" || selectedValue === "poor") {
                trackAction('custom');
                window.location.href = "review/index.php?id=<?php echo $id; ?>";  // Corrected ID
            }
        }

        window.onload = function() {
            trackAction('visit');
        };

        window.onbeforeunload = function() {
            const selected = document.querySelector('input[name="view"]:checked');
            if (!selected) {
                trackAction('inactive');
            }
        };
    </script>
    <link href="css/style1.css" rel="stylesheet" type="text/css" media="all" />
    <link href="//fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
</head>

<body class="agileits_w3layouts">
    <div class="w3layouts_main wrap">
        <form action="feedback.php" method="post" class="agile_form">
            <div class="imagealign">
                <img src="./images/logo4.png" alt="Google icon">
            </div>
            <ul class="agile_info_select">
                <li>
                    <input type="radio" name="view" value="excellent" id="excellent" required onclick="handleRedirect(event)">
                    <label for="excellent">excellent</label>
                    <div class="check w3"></div>
                </li>
                <li>
                    <input type="radio" name="view" value="good" id="good" onclick="handleRedirect(event)">
                    <label for="good"> good</label>
                    <div class="check w3ls"></div>
                </li>
                <li>
                    <input type="radio" name="view" value="neutral" id="neutral" onclick="handleRedirect(event)">
                    <label for="neutral">neutral</label>
                    <div class="check wthree"></div>
                </li>
                <li>
                    <input type="radio" name="view" value="poor" id="poor" onclick="handleRedirect(event)">
                    <label for="poor">poor</label>
                    <div class="check w3_agileits"></div>
                </li>
            </ul>
        </form>
    </div>
    <div class="agileits_copyright text-center">
        <p>Copyright © 2025 <a href="https://yashinfosystem.com/" target="_blank">yashinfosystems</a></p>
    </div>
</body>

</html>


