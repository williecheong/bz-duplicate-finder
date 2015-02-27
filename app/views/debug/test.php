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
        <toaster-container toaster-options="{'time-out': 5000}"></toaster-container>
        <div class="container">
            <h1>Bugzilla Duplicate Finder - Functional Testing</h1>
            <table class="table table-hover table-bordered table-condensed">
                <!-- Table Headings -->
                <thead>
                    <tr>
                        <th>Input Bug List</th>
                        <th>Expected Clusters</th>
                        <th>Actual Clusters Output</th>
                        <th>Execution Details</th>
                    </tr> 
                </thead>
                <tbody>
                    <tr ng-repeat="testCase in testCases">
                        <td>
                            <span ng-bind="testCase.input | arrayToCsv"></span>
                        </td>
                        <td>
                            <div ng-repeat="group in testCase.expected">
                                <span ng-bind="group | arrayToCsv"></span>
                                <hr style="margin:0px;">
                            </div>
                        </td>
                        <td>
                            <div ng-if="!testCase.output" class="text-center">
                                <button class="btn btn-primary">
                                    <i class="fa fa-terminal"></i> Run test
                                </button>
                            </div>
                            <div ng-repeat="group in testCase.output">
                                <span ng-bind="group | arrayToCsv"></span>
                                <a href="">
                                    <i class="fa fa-desktop"></i>
                                </a>
                                <hr style="margin:0px;">
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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