var app = angular.module('myApp', ['ui.bootstrap', 'ngtimeago', 'toaster']);

app.controller('myController', function( $scope, $sce, $http, $filter, $modal, toaster ) {
    
    $scope.loadEvent = function() {
        $http({
            'method': 'GET',
            'url': '/api/v1/event?id='+$clubhouse.eventId
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

    $scope.loadParticipants = function() {
        $http({
            'method': 'GET',
            'url': '/api/v1/event/participant/'+$scope.event.id
        }).success(function(data, status, headers, config) {
            $scope.event.participants = data;
        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
        });
    };

    $scope.addNewParticipant = function(email) {
        $http({
            'method': 'PUT',
            'url': '/api/v1/event/participant/'+$clubhouse.eventId,
            'data': {
                'email' : email
            }
        }).success(function(data, status, headers, config) {
            toaster.pop('success', 'Success: ' + status, data.message);
            $scope.loading = false;
            $scope.initialize();            
        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
        });
    };

    $scope.initialize = function() {
        $scope.event = {};  
        $scope.showAddUser = false;
        $scope.newParticipantEmail = "";
        $scope.loadEvent();
    };

    $scope.initialize();

    $scope.openModal = function(modalTitle) {
        var modalInstance = $modal.open({
            templateUrl: modalTitle + '.html',
            controller: modalTitle + 'ModalController',
            size: 'lg',
            resolve: {
                event: function () {
                    return $scope.event;
                },
                initialize: function () {
                    return $scope.initialize;
                }
            }
        });
    };

    $scope.logout = function() {
        $scope.loading = true;
        $http({
            'method': 'GET',
            'url': '/api/v1/user/logout'
        }).success(function(data, status, headers, config) {
            toaster.pop('success', 'Success: ' + status, data.message);
            setTimeout(function() {
                window.location.href = '/';
            }, 1500);

        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
            $scope.loading = false;
        });
    };

}).filter('paymentStatus', function() {
  return function(payment) {
    return (payment==1) ? "Paid" : "Unpaid" ;
  };
}).filter('restrictionStatus', function() {
  return function(restriction) {
    return (restriction==1) ? "Member Exclusive" : "Open Event" ;
  };
}).directive('validNumber', function() {
    return {
        require: '?ngModel',
        link: function(scope, element, attrs, ngModelCtrl) {
            if(!ngModelCtrl) {
                return; 
            }

            ngModelCtrl.$parsers.push(function(val) {
                var clean = val.replace( /[^0-9]+/g, '');
                if (val !== clean) {
                    ngModelCtrl.$setViewValue(clean);
                    ngModelCtrl.$render();
                }
                return clean;
            });

            element.bind('keypress', function(event) {
                if(event.keyCode === 32) {
                    event.preventDefault();
                }
            });
        }
    };
}).directive('validNumberPhone', function() {
    return {
        require: '?ngModel',
        link: function(scope, element, attrs, ngModelCtrl) {
            if(!ngModelCtrl) {
                return; 
            }

            ngModelCtrl.$parsers.push(function(val) {
                var clean = val.replace( /[^0-9|-]+/g, '');
                if (val !== clean) {
                    ngModelCtrl.$setViewValue(clean);
                    ngModelCtrl.$render();
                }
                return clean;
            });

            element.bind('keypress', function(event) {
                if(event.keyCode === 32) {
                    event.preventDefault();
                }
            });
        }
    };
});

angular.module('myApp').controller('eventModalController', function ($scope, $modalInstance, $http, toaster, initialize, event) {
    $scope.dateOptions = { };
    $scope.selectOptions = {
        "restrictions" : [
            { "value" : 1, "label" : "Member Exclusive" },
            { "value" : 0, "label" : "Open Event" }
        ]
    };

    $scope.initializeModal = function() {
        $scope.input = { 
            "id" : event.id,
            "name" : event.name,
            "restriction" : parseInt(event.restriction),
            "included" : event.included,
            "playerCost" : event.playerCost,
            "start" : event.start,
            "description" : event.description,
            "rules" : event.rules
        };
        console.log($scope.input);
    };

    $scope.openCalendar = function($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.showCalendar = true;
    };

    $scope.submit = function (input) {
        $scope.loading = true;
        $http({
            'method': 'PUT',
            'url': '/api/v1/event',
            'data': input
        }).success(function(data, status, headers, config) {
            toaster.pop('success', 'Success: ' + status, data.message);
            $scope.loading = false;
            initialize();
            $modalInstance.close();
            
        }).error(function(data, status, headers, config) {
            toaster.pop('error', 'Error: ' + status, data.message);
            $scope.loading = false;
        });
    };

    $scope.initializeModal();
});