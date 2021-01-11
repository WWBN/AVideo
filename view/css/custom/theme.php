<?php
$theme = "default";
if(!empty($_REQUEST['theme'])){
    $theme = $_REQUEST['theme'];
}
?>
<!DOCTYPE html>
<html lang="us">
    <head>
        <title>Theme <?php echo ucfirst($theme); ?> Test</title>
        <link href="../../css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="customCSS">
        <link href="../../css/custom/<?php echo $theme; ?>.css" rel="stylesheet" type="text/css" id="customCSS">
        <link href="../../css/main.css" rel="stylesheet" type="text/css">
    </head>
    <body class="" >
        <nav class="navbar navbar-default navbar-fixed-top " id="mainNavBar">
            <ul class="items-container">
                <li>
                    <ul class="left-side">
                        <li style="max-width: 40px;">
                            <button class="btn btn-default navbar-btn pull-left alreadyTooltip" id="buttonMenu" data-toggle="tooltip" title="" data-placement="right" data-original-title="Main Menu"><span class="fa fa-bars"></span></button>

                        </li>
                        <li style="width: 100%; text-align: center;">
                            <a class="navbar-brand" id="mainNavbarLogo" href="#">
                                <img src="../../../videos/userPhoto/logo.png" alt="AVideo" class="img-responsive ">
                            </a>
                        </li>

                    </ul>
                </li>

                <li style="margin-right: 0px; padding-left: 0px;" id="navbarRegularButtons">
                    <div class=" in" id="myNavbar">
                        <ul class="right-menus" style="padding-left: 0;">
                            <li>
                                <a href="#" class="btn btn-danger navbar-btn alreadyTooltip" data-toggle="tooltip" title="" data-placement="bottom" data-original-title="Broadcast a Live Stream">
                                    <span class="fa fa-circle"></span>  <span >LIVE</span>
                                </a>
                            </li>
                            <li class="dropdown">
                                <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown">
                                    <span class="fa fa-bell"></span>
                                    <span class="badge onlineApplications" style=" background: rgba(255,0,0,1); color: #FFF;">0</span>
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right notify-drop notfound" id="availableLiveStream"><li class="" style="margin-right: 0;">
                                        <a href="#" class="liveLink">
                                            <div class="pull-left" style="">
                                                <img src="" class="img img-circle img-responsive" style="max-width: 38px;">
                                            </div>
                                            <div style="">
                                                <i class="fas fa-ban"></i> <strong class="liveTitle">There is no streaming now</strong> <br>
                                                <span class="label liveUser label-danger"></span> <span class="label label-danger liveNow faa-flash faa-slow animated hidden">LIVE NOW</span>
                                            </div>
                                        </a>
                                    </li></ul>
                            </li>
                            <li class="hidden liveModel" style="margin-right: 0;">
                                <a href="#" class="liveLink ">
                                    <div class="pull-left">
                                        <img src="" class="img img-circle img-responsive" style="max-width: 38px;">
                                    </div>
                                    <div style="margin-left: 40px;">
                                        <i class="fas fa-video"></i> <strong class="liveTitle">Title</strong> <br>
                                        <span class="label label-success liveUser">User</span> <span class="label label-danger liveNow faa-flash faa-slow animated hidden">LIVE NOW</span>
                                    </div>
                                </a>
                            </li>
                            <li style="padding-right: 40px;">
                                <div class="btn-group alreadyTooltip" data-toggle="tooltip" title="" data-placement="left" data-original-title="Submit your videos">
                                    <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-video"></i>  <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                        <li>
                                            <a href="#" data-toggle="tooltip" title="" data-placement="left" data-original-title="Upload a file or download it from the Internet" class="alreadyTooltip">
                                                <span class="fa fa-cog"></span> Encode video and audio                </a>
                                        </li>
                                        <li>
                                            <a href="#" data-toggle="tooltip" title="" data-placement="left" data-original-title="Embed videos/files in your site" class="alreadyTooltip">
                                                <span class="fa fa-link"></span> Embed a video link                                                    </a>
                                        </li>
                                        <li>
                                            <a href="#" data-toggle="tooltip" title="" data-placement="left" data-original-title="Write an article" class="alreadyTooltip">
                                                <i class="far fa-newspaper"></i> Add Article                                                    </a>
                                        </li>
                                        <li><a href="#"><span class="fa fa-link"></span> Bulk Embed</a></li>                                        </ul>     
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="container-fluid">
            <br>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo ucfirst($theme); ?>
                </div>
                <div class="panel-body">
                    <div class="btn-group-justified">
                        <a href="#" class="btn btn-success">Success</a>
                        <a href="#" class="btn btn-danger">Danger</a>
                        <a href="#" class="btn btn-warning">Warning</a>
                        <a href="#" class="btn btn-primary">Primary</a>
                        <a href="#" class="btn btn-info">Info</a>
                        <a href="#" class="btn btn-default">Default</a>
                    </div>
                </div>
            </div>

        </div><!--/.container-->
        <div class="clearfix"></div>
        <footer style="position: fixed; bottom: 0px; width: 100%;" id="mainFooter">
            <ul class="list-inline">
                <li>
                    Powered by Theme
                </li>

            </ul>
        </footer>
    </body>
</html>