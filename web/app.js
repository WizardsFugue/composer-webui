
var composerWebuiApp = angular.module('composerWebuiApp', []);


composerWebuiApp.controller('TabsCtrl', function ($scope) {
    $scope.tabs = [{
        title: 'Overview',
        url: 'overview.tpl.html'
    }, {
        title: 'Composer.json',
        url: 'composer_json.tpl.html'
    }, {
        title: 'Three',
        url: 'three.tpl.html'
    }];

    $scope.currentTab = 'overview.tpl.html';

    $scope.onClickTab = function (tab) {
        $scope.currentTab = tab.url;
    }

    $scope.isActiveTab = function(tabUrl) {
        return tabUrl == $scope.currentTab;
    }
})

composerWebuiApp.controller('ComposerJsonCtrl', function ($scope, $http) {

    $http.get('api/composer.json').success(function(data) {
        $scope.file  = data.file;
    });
})

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


