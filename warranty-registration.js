/**
 * Created by Boris on 12/6/2015.
 */
(function(){
  "use strict";

  var app = angular.module('warranty-registration-app', []);


  app.config(function ($httpProvider) {
    // send all requests payload as query string
    $httpProvider.defaults.transformRequest = function(data){
      if (data === undefined) {
        return data;
      }
      return jQuery.param(data);
    };

    // set all post requests content type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
  });

  app.controller('WarrantyRegistration', WarrantyRegistration);

  WarrantyRegistration.$inject = ['$scope', '$http'];

  function WarrantyRegistration($scope, $http){
    var vm = this;
  }
})();