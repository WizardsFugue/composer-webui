
var composerWebuiApp = angular.module('composerWebuiApp', []);

composerWebuiApp.controller('OverviewCtrl', function ($scope, $http) {
    $scope.packagesOrder = 'prettyName';
    $scope.project = {};

    $http.get('api/validate').success(function(data) {
        $scope.project.validationResult  = data.validation;
    });

    $http.get('api/').success(function(data) {
        $scope.project.name  = data.name;
        $scope.packages = data.packages;

        var licenses = {};
        angular.forEach($scope.packages, function(obj, key) {
            angular.forEach(obj.license, function(value) {
                licenses[value] = 1;
            })
        });
        var usedLicenses = []
        for (var key in licenses) {
            usedLicenses.push(key);
        }
        $scope.usedLicenses = usedLicenses;
    });
});


