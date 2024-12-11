<!DOCTYPE html>
<html>
<head>
    <?php include "header.php"; ?>
    <title>Scan Kartu</title>

    <!-- scanning membaca kartu RFID -->
    <script type="text/javascript">
        $(document).ready(function() {
            setInterval(function(){
                $("#cekkartu").load('bacakartu.php')
            }, 1000);
        });
    </script> 
</head>
<body>

    <?php include "menu.php"; ?>

    <!-- isi -->
    <div class="container-fluid" style="padding-top: 10%">
        <div id="cekkartu"></div>
    </div>
    <br>

    <?php include "footer.php"; ?>

</body>
</html>