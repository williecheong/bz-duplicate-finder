var app = angular.module('myApp', ['ui.bootstrap', 'ngtimeago', 'toaster']);

app.controller('myController', function( $scope, $sce, $http, $filter, toaster ) {
    
    $scope.testCases = testCases;
    $scope.runTest = function(key) {
        $scope.testCases[key].loading = true;
        $http({
            'method': 'GET',
            'url': '/duplicates?debug=1&bugs=' + $scope.testCases[key].input.join(',')
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
        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
            $scope.testCases[key].loading = false;
        });
    };

    $scope.runAllTests = function(key) {
        $scope.loading = true;
        $scope.testCases[key].loading = true;
        $http({
            'method': 'GET',
            'url': '/duplicates?debug=1&bugs=' + $scope.testCases[key].input.join(',')
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

            if (typeof $scope.testCases[key+1] != 'undefined') {
                $scope.runAllTests(key+1);
            } else {
                $scope.loading = false;
            }

        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
            $scope.testCases[key].loading = false;
        });
    };

    $scope.validate = function(expected, actual) {
        var missingGroups = [];
        for (var i=0; i<expected.length; i++) {
            var groupFound = false;
            for (var j=0; j<actual.length; j++) {
                if ($scope.sameArray(expected[i], actual[j].bugs)) {
                    groupFound = true;
                    break;
                }
            }
            if (groupFound == false) {
                missingGroups.push(i);
            }
        } 
        
        if (missingGroups.length > 0) {
            return {"type":"fail", "reason": (expected.length-missingGroups.length)+" out of "+expected.length+" groups found. Groups ["+missingGroups.join(', ')+"] are missing from actual output groups."};
        }

        if (expected.length < actual.length) {
            return {"type":"warning", "reason":"All expected groups were found in the output but more groups were generated than expected."};
        }

        return {"type":"success"};
    };

    $scope.sameArray = function (a, b) {
        if (a.length != b.length) {
            return false;
        }
        a.sort(); b.sort();
        for (i=0; i<a.length; i++) {
            if (a[i] != b[i]) {
                return false;
            }
        }
        return true;
    };

}).filter('arrayToCsv', function() {
  return function(input) {
    if (typeof input != 'object') { return input; }
    if (input.length < 0) { return "Empty"; }
    return input.join(', ');
  };
}).filter('bugLink', function() {
  return function(input) {
    return "https://bugzilla.mozilla.org/rest/bug?include_fields=id,summary,component,product,dupe_of&bug_id=" + input.join(', ');
  };
}).filter('bmoLink', function() {
  return function(input) {
    return "https://bugzilla.mozilla.org/buglist.cgi?quicksearch=" + input.join(', ');
  };
});