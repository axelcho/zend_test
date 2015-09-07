var app = angular.module('Zend_Test', ['ngRoute', 'ngFileUpload', 'xeditable']);

app.run(function (editableOptions) {
    editableOptions.theme = 'default';
});


//This configures the routes and associates each route with a view and a controller
app.config(function ($routeProvider) {
    $routeProvider
        //#upload
        .when('/upload',
        {
            controller: 'UploadController',
            templateUrl: '/view/upload.html'
        })

        //#mapping
        .when('/mapping',
        {
            controller: 'MappingController',
            templateUrl: '/view/mapping.html'
        })

        //#preview
        .when('/preview',
        {
            controller: 'PreviewController',
            templateUrl: '/view/preview.html'
        })
        .otherwise({redirectTo: '/upload'});
});


app.service('StorageService', function () {
    var users = [];
    var columns = 0;
    var password = '';
    var status = '';
    var target = '';
    var map = [];

    var setUsers = function (data) {
        users = data;
    };
    var getUsers = function () {
        return users;
    };

    var setColumns = function (data) {
        columns = data;
    };

    var getColumns = function () {
        return columns;
    };

    var setPassword = function (data) {
        password = data;
    };
    var getPassword = function () {
        return password;
    };

    var setStatus = function (data) {
        status = data;
    };

    var getStatus = function () {
        return status;
    };

    var setTarget = function (data) {
        target = data;
    };

    var getTarget = function () {
        return target;
    };

    var setMap = function (data) {
        map = data;
    };

    var getMap = function () {
        return map;
    };


    return {
        setUsers: setUsers,
        getUsers: getUsers,
        setColumns: setColumns,
        getColumns: getColumns,
        setPassword: setPassword,
        getPassword: getPassword,
        setStatus: setStatus,
        getStatus: getStatus,
        setTarget: setTarget,
        getTarget: getTarget,
        setMap: setMap,
        getMap: getMap
    }
});


app.controller('UploadController', ['$scope', 'Upload', '$location', 'StorageService', function ($scope, Upload, $location, StorageService) {

    $scope.userStatus = ['active', 'suspended', 'disabled'];

    //ajax upload using ng-upload
    $scope.upload = function (file) {
        file.upload = Upload.upload({
            url: 'api/upload',
            method: 'POST',
            headers: {
                'my-header': 'my-header-value'
            },
            fields: {},
            file: file,
            fileFormDataName: 'xlsFile'
        }).then(function (response) {
                if (response.data.parsedData) {

                    StorageService.setUsers(response.data.parsedData);
                    StorageService.setColumns(parseInt(response.data.columns));
                    StorageService.setTarget(response.data.target);
                    StorageService.setStatus($scope.status);
                    StorageService.setPassword($scope.password);

                    $location.path("/mapping");
                }


                if (response.data.error) {
                    alert(response.data.error);
                }
            }
        );
    }
}]);

app.controller('MappingController', ['$scope', '$location', 'StorageService', function ($scope, $location, StorageService) {


    $scope.users = StorageService.getUsers();
    $scope.password = StorageService.getPassword();
    $scope.currentStatus = StorageService.getStatus();
    $scope.userStatus = ['active', 'suspended', 'disabled'];


    columns = StorageService.getColumns();
    keys = ['firstname', 'lastname', 'email', 'country', 'city', 'address', 'password', 'status'];

    $scope.keys = keys;
    $scope.fields = [];
    for (i = 0; i < columns; i++) {
        field = {
            default: keys[i],
            name: 'field' + i
        };

        $scope.fields.push(field);
    }

    $scope.preview = function () {

        map = [];
        for (j = 0; j < $scope.fields.length; j++) {
            mappedField = $scope.fields[j];
            map.push(mappedField.name);
        }
        StorageService.setMap(map);
        $location.path("/preview");


    }
}]);

app.controller('PreviewController', ['$scope', '$location', '$http', 'StorageService', function ($scope, $location, $http, StorageService) {

    $scope.users = StorageService.getUsers();
    $scope.map = StorageService.getMap();
    $scope.password = StorageService.getPassword();
    $scope.status = StorageService.getStatus();
    target = StorageService.getTarget();

    $scope.backToMapping = function () {
        $location.path("/mapping");
    }

    $scope.saveToDatabase = function () {

        $http.post('api/save',
            {
                map: $scope.map,
                target: target,
                password: $scope.password,
                status: $scope.status
            }).then(function (response) {
                if (response.data.success){
                    alert(response.data.success + 'users added successfully');
                }
                if (response.data.error){
                    alert(response.data.error);
                }
            }
        );
    }

}]);
