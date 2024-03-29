<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NETWORK</title>
    <link rel="stylesheet" href="<?='assets/style.css'?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<script>
    $(document).ready(function(){
        $('.modal').modal();
        $('.sidenav').sidenav();
        $("#loadingModal").modal({dismissible: true, startingTop: '40%', endingTop: '40%'});
        $('.dropdown-trigger').dropdown();
    });
</script>
<style>
    #loadingModal{
        width: 150px!important;
        height: 150px!important;

    }
    .loading_center{
        height:100%;display: flex;
        align-items: center;
        justify-content: center;
    }
    .wg_shadow {
        -webkit-box-shadow: 0 1px 15px 1px rgba(81, 77, 92, .08);
        box-shadow: 0 1px 15px 1px rgba(81, 77, 92, .08);
    }
</style>
<nav class="black" style="border-bottom: 1px solid#dbdbdb">
    <div class="nav-wrapper container">
        <a href="#!" class="brand-logo">M2X-PROXY</a>
        <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
            <?php if(session('authenticated')): ?>
                <li><a href="index.php">Main</a></li>
                <li><a href="usb-connections.php">Connections</a></li>
                <li><a href="options.php">Options</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<ul class="sidenav" id="mobile-demo">
    <?php if(session('authenticated')): ?>
        <li><a href="index.php">Main</a></li>
        <li><a href="usb-connections.php">Connections</a></li>
        <li><a href="options.php">Options</a></li>
        <li><a href="logout.php">Logout</a></li>
    <?php endif; ?>
</ul>

<div id="loadingModal" class="modal">
    <div class="center loading_center">
        <div class="preloader-wrapper big active">
            <div class="spinner-layer spinner-blue-only">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>
</div>