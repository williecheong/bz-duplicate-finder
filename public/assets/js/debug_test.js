var app = angular.module('myApp', ['ui.bootstrap', 'ngtimeago', 'toaster']);

app.controller('myController', function( $scope, $sce, $http, $filter, $modal, toaster ) {
    
    $scope.testCases = [
        {
            input : [123123,324234],
            expected : [
                [123123,345346],
                [123124,3453346],
            ],
            output : [
                [1235325,345346],
            ]
        }
    ];

    $scope.loadEvent = function() {
        $http({
            'method': 'GET',
            'url': '/api/v1/event?id='
        }).success(function(data, status, headers, config) {
            if (data.length > 0) {
                $scope.event = data[0];
                $scope.loadParticipants();
            } else {
                toaster.pop('error', 'Error: ' + status, "Event not found");
            }
        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
        });
    }; 

}).filter('arrayToCsv', function() {
  return function(input) {
    return (typeof input == 'array') ? input.join(',') : input ;
  };
});