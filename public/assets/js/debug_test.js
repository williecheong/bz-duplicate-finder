var app = angular.module('myApp', ['ui.bootstrap', 'ngtimeago', 'toaster']);

app.controller('myController', function( $scope, $sce, $http, $filter, toaster ) {
    
    $scope.testCases = testCases;
    $scope.runTest = function(key, bugList) {
        $scope.testCases[key].loading = true;
        $http({
            'method': 'GET',
            'url': '/duplicates?debug=1&bugs=' + bugList.join(',')
        }).success(function(data, status, headers, config) {
            $scope.testCases[key].loading = false;
            $scope.testCases[key].outputGroups = data.duplicates;
            $scope.testCases[key].meta = {
                similarityRequirement : data.similarityRequirement,
                runtime : data.runtimeInSeconds,
                result : $scope.validate(
                    $scope.testCases[key].expectedGroups,
                    data.duplicates
                )
            };

            if (data.duplicates.length == 0) {
                toaster.pop('info', 'Status: ' + status, "No duplicate groups found for test case " + key + ".");
            }

        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
            $scope.testCases[key].loading = false;
        });
    }; 

    $scope.validate = function(expected, actual) {
        if (expected.length != actual.length) {
            return {"type":"fail", "reason":"Wrong number of bug groups returned."};
        }
        return {"type":"success"};
    };

}).filter('arrayToCsv', function() {
  return function(input) {
    if (typeof input != 'object') { return input; }
    if (input.length == 0) { return "Empty"; }
    return input.join(', ');
  };
}).filter('bugLink', function() {
  return function(input) {
    return "https://bugzilla.mozilla.org/rest/bug?include_fields=id,summary,component,product&bug_id=" + input.join(', ');
  };
});