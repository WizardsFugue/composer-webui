
var composerWebuiApp = angular.module('composerWebuiApp', []);

composerWebuiApp.controller('OverviewCtrl', function ($scope, $http) {
    $http.get('api/').success(function(data) {
        $scope.project  = { "name":data.name};
        $scope.packages = data.packages;
    });
});


