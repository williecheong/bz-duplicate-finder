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
        <style>
            .container {
                width: 95%;
            }
        </style>
    </head>
    <body ng-app="myApp" ng-controller="myController">
        <toaster-container toaster-options="{'time-out': 5000}"></toaster-container>
        <div class="container">
            <h1>
                Bugzilla Duplicate Finder - Functional Testing
                <button ng-click="runAllTests(0)" ng-disabled="loading" class="btn btn-success btn-lg pull-right">
                    <span ng-hide="loading">
                        <i class="fa fa-globe fa-lg"></i> 
                        Run All Tests
                    </span>
                    <span ng-show="loading">
                        <i class="fa fa-spinner fa-spin fa-lg"></i> 
                        Running Now
                    </span>
                </button>
            </h1>
            <table class="table table-hover table-bordered table-condensed">
                <!-- Table Headings -->
                <thead>
                    <tr>
                        <th style="width:10%;">Case #</th>
                        <th style="width:15%;">Input Bug List</th>
                        <th style="width:20%;">Expected Groups</th>
                        <th style="width:30%;">Actual Groups Output</th>
                        <th style="width:25%;">Execution Details</th>
                    </tr> 
                </thead>
                <tbody>
                    <tr ng-class="{'warning':testCase.loading}" ng-repeat="(index,testCase) in testCases">
                        <td>
                            <button ng-click="runTest(index)" ng-disabled="testCase.loading" class="btn btn-primary">
                                <span ng-hide="testCase.loading">
                                    <i class="fa fa-terminal fa-lg"></i>
                                </span> 
                                <span ng-show="testCase.loading">
                                    <i class="fa fa-spinner fa-spin fa-lg"></i>
                                </span>
                                {{ index }}
                            </button>
                        </td>
                        <td>
                            <a href="{{testCase.input | bmoLink}}" target="_blank"><i class="fa fa-desktop"></i></a>
                            <a href="{{testCase.input | bugLink}}" target="_blank"><i class="fa fa-database"></i></a>
                            <span ng-bind="testCase.input | arrayToCsv"></span>
                            <hr style="margin-top:0px;margin-bottom:20px;">
                            <div ng-if="testCase.notes">
                                <strong>Notes:</strong><br>
                                <em ng-bind="testCase.notes"></em>
                            </div>
                        </td>
                        <td>
                            <div ng-repeat="(groupNum, group) in testCase.expectedGroups">
                                <strong>Group {{groupNum}}: </strong><br>
                                <a href="{{group | bmoLink}}" target="_blank"><i class="fa fa-desktop"></i></a>
                                <a href="{{group | bugLink}}" target="_blank"><i class="fa fa-database"></i></a>
                                <span ng-bind="group | arrayToCsv"></span>
                                <hr style="margin-top:0px;margin-bottom:20px;">
                            </div>
                        </td>
                        <td>
                            <div ng-if="!testCase.outputGroups || testCase.outputGroups.length==0">
                                <em>Empty...</em>
                            </div>
                            <div ng-repeat="group in testCase.outputGroups">
                                <strong>Bugs:</strong> 
                                <a href="{{group.bugs | bmoLink}}" target="_blank"><i class="fa fa-desktop"></i></a>
                                <a href="{{group.bugs | bugLink}}" target="_blank"><i class="fa fa-database"></i></a>
                                <span ng-bind="group.bugs | arrayToCsv"></span><br>
                                <strong>Keywords:</strong> 
                                <span ng-bind="group.keywords | arrayToCsv"></span><br>
                                <strong>Similarity:</strong> 
                                <span ng-bind="group.similarity | number:6"></span>
                                <hr style="margin-top:0px;margin-bottom:20px;">
                            </div>
                        </td>
                        <td>
                            <div ng-if="testCase.meta">
                                <div style="margin-top:0px;margin-bottom:10px;">
                                    <div>
                                        <span>Status:</span>
                                        <strong ng-class="{'text-success':testCase.meta.result.type=='success','text-danger':testCase.meta.result.type=='fail','text-warning':testCase.meta.result.type=='warning'}">
                                            {{ testCase.meta.result.type | uppercase}}
                                        </strong>
                                    </div>
                                    <em ng-if="testCase.meta.result.reason" ng-bind="testCase.meta.result.reason"></em>
                                </div>
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Number of Groups Returned:</span>
                                    <strong>{{ testCase.outputGroups.length }}</strong>
                                </div>      
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Total Run Duration:</span>
                                    <strong>{{ testCase.meta.runtime.totalRuntime | number:4 }} seconds</strong>
                                </div>
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Bugzilla Retrieval:</span>
                                    <strong>{{ testCase.meta.runtime.bugzillaForBugs | number:4 }} seconds</strong>
                                </div>         
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>NLProcessing:</span>
                                    <strong>{{ testCase.meta.runtime.bugsToBagsOfWords | number:4 }} seconds</strong>
                                </div>         
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Cross Pairing:</span>
                                    <strong>{{ testCase.meta.runtime.bagOfWordsToSimilarPairs | number:4 }} seconds</strong>
                                </div>         
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Bug Grouping:</span>
                                    <strong>{{ testCase.meta.runtime.similarPairsToGroups | number:4 }} seconds</strong>
                                </div>         
                                <div style="margin-top:0px;margin-bottom:5px;">
                                    <span>Post Processing:</span>
                                    <strong>{{ testCase.meta.runtime.postProcessingForBugGroups | number:4 }} seconds</strong>
                                </div>    
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Angular / Bootstrap Core JavaScript
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.11.0/ui-bootstrap-tpls.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-animate.min.js"></script>
        <script src="//dropbox.com/s/5xks0xxfz1mmqux/test_cases.js?dl=1"></script>    
        <script src="/assets/vendor/ng-timeago/ng-timeago.js"></script>
        <script src="/assets/vendor/ng-toaster/toaster.js"></script>
        <script src="/assets/js/debug_test.js"></script>
    </body>
</html>