var app = angular.module('Zend_Test', ['ngRoute', 'ngFileUpload', 'xeditable']);

app.run(function(editableOptions) {
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
        .otherwise({ redirectTo: '/upload' });
});


app.controller('UploadController', ['$scope', 'Upload', '$rootScope', '$location', function ($scope, Upload, $rootScope, $location) {
    //initialize rootScope
    $rootScope.users = [];
    $scope.userStatus = ['active', 'suspended', 'disabled'];

    //ajax upload using ng-upload
    $scope.upload = function(file) {
        file.upload = Upload.upload({
            url: 'api/upload',
            method: 'POST',
            headers: {
                'my-header': 'my-header-value'
            },
            fields: {'password': $scope.password, 'status': $scope.status},
            file: file,
            fileFormDataName: 'xlsFile'
        }).then(function(response) {
                if (response.data.parsedData) {
                    $rootScope.users = response.data.parsedData;
                    $location.path("/mapping");
                }

                if (response.data.error){
                    alert(response.data.error);
                }
            }
        );
    }
}]);

app.controller('MappingController', ['$scope', '$rootScope', '$location', function($scope, $rootScope, $location){
    $scope.users = [];
    $scope.currentPassword = undefined;
    $scope.currentStatus = undefined;

    $scope.userStatus = ['active', 'suspended', 'disabled'];

    users = $rootScope.users;
    if (users && users.length > 0) {
        $scope.users = users;
        first = users[0];
        $scope.currentPassword = first.password;
        $scope.currentStatus = first.status;
    }

    $scope.saveUser = function(data, index){
        $scope.users[index] = data;
    };

    $scope.removeUser = function(index){
        $scope.users.splice(index, 1);
    };

    $scope.preview = function(){
        editedUsers = $scope.users;
        if (editedUsers && editedUsers.length > 0){
            for (i = 0; i< editedUsers.length; i++){
                editedUsers[i].password = $scope.currentPassword;
                editedUsers[i].status = $scope.currentStatus;
            }
        }

        $rootScope.users = editedUsers;
        $location.path("/preview");
    }
}]);

app.controller('PreviewController', ['$scope', '$rootScope', '$location', '$http', function($scope, $rootScope, $location, $http){
    $scope.users = [];

    users = $rootScope.users;
    if (users && users.length > 0){
        $scope.users = users;
    }

    $scope.backToMapping = function(){
        $location.path("/mapping");
    }

    $scope.saveToDatabase = function(){
        jsonUsers = JSON.stringify($scope.users);
        $http.post('api/save', {users: $scope.users}).then(function(response){
                console.log(response);
            }
        );
    }

}]);