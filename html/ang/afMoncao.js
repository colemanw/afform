(function(angular, $, _) {
  angular.module('afMoncao', CRM.angRequires('afMoncao'));

  // "afMonaco" is a basic skeletal directive.
  // Example usage: <div af-monaco ng-model="my.content"></div>
  angular.module('afMoncao').directive('afMonaco', function($timeout) {
    return {
      restrict: 'AE',
      require: 'ngModel',
      template: '<div class="af-monaco-container" style="width:800px;height:600px;border:1px solid grey"></div>',
      link: function($scope, $el, $attr, ngModel) {
        var editor;
        require.config({paths: CRM.afMoncao.paths});
        require(['vs/editor/editor.main'], function() {
          var editorEl = $el.find('.af-monaco-container');
          editor = monaco.editor.create(editorEl[0], {
            value: ngModel.$modelValue,
            language: 'html',
            theme: 'vs-dark',
            minimap: {
              enabled: false
            },
            scrollbar: {
              useShadows: false,
              verticalHasArrows: true,
              horizontalHasArrows: true,
              vertical: 'visible',
              horizontal: 'visible',
              verticalScrollbarSize: 17,
              horizontalScrollbarSize: 17,
              arrowSize: 30
            }
          });

          editor.onDidChangeModelContent(_.debounce(function () {
            $scope.$apply(function () {
              ngModel.$setViewValue(editor.getValue());
            });
          }, 150));

          ngModel.$render = function() {
            if (editor) {
              editor.setValue(ngModel.$modelValue);
            }
            // FIXME: else: retry?
          };

          // FIXME: This makes vertical scrolling much better, but horizontal is still weird.
          var origOverflow;
          function bodyScrollSuspend() {
            if (origOverflow !== undefined) return;
            origOverflow = $('body').css('overflow');
            $('body').css('overflow', 'hidden');
          }
          function bodyScrollRestore() {
            if (origOverflow === undefined) return;
            $('body').css('overflow', origOverflow);
            origOverflow = undefined;
          }
          editorEl.on('mouseenter', bodyScrollSuspend);
          editorEl.on('mouseleave', bodyScrollRestore);
          editor.onDidFocusEditorWidget(bodyScrollSuspend);
          editor.onDidBlurEditorWidget(bodyScrollRestore);

          $scope.$on('$destroy', function () {
            bodyScrollRestore();
            if (editor) editor.dispose();
          });
        });
      }
    };
  });

})(angular, CRM.$, CRM._);
