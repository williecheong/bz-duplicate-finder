<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="img/ico" href="/assets/img/favicon.ico">

        <title>Functional Testing</title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="/assets/vendor/ng-toaster/toaster.css" rel="stylesheet">
    </head>
    <body ng-app="myApp" ng-controller="myController">
        <toaster-container toaster-options="{'time-out': <?=Config::get('constants.TOASTER_FADE_OUT')?>}"></toaster-container>
        <div class="container" id="outer-container">
            HELLO
        </div> <!-- /container -->

        <!-- Angular / Bootstrap Core JavaScript
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.11.0/ui-bootstrap-tpls.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-animate.min.js"></script>
        <script src="/assets/vendor/ng-timeago/ng-timeago.js"></script>
        <script src="/assets/vendor/ng-toaster/toaster.js"></script>
        <script src="/assets/js/debug_test.js"></script>
    </body>
</html>