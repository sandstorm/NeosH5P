/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = readFromConsumerApi;
function readFromConsumerApi(key) {
    return function () {
        if (window['@Neos:HostPluginAPI'] && window['@Neos:HostPluginAPI']['@' + key]) {
            var _window$NeosHostPlu;

            return (_window$NeosHostPlu = window['@Neos:HostPluginAPI'])['@' + key].apply(_window$NeosHostPlu, arguments);
        }

        throw new Error('You are trying to read from a consumer api that hasn\'t been initialized yet!');
    };
}

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().React;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().PropTypes;

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().NeosUiDecorators;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().ReactUiComponents;

/***/ }),
/* 5 */,
/* 6 */,
/* 7 */,
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(9);

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _neosUiExtensibility = __webpack_require__(10);

var _neosUiExtensibility2 = _interopRequireDefault(_neosUiExtensibility);

var _index = __webpack_require__(15);

var _index2 = _interopRequireDefault(_index);

var _index3 = __webpack_require__(20);

var _index4 = _interopRequireDefault(_index3);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

(0, _neosUiExtensibility2.default)('Sandstorm.NeosH5P:ContentPickerEditor', {}, function (globalRegistry) {
    var editorsRegistry = globalRegistry.get('inspector').get('editors');
    var secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');

    editorsRegistry.set('Sandstorm.NeosH5P/ContentPickerEditor', {
        component: _index4.default
    });

    secondaryEditorsRegistry.set('Sandstorm.NeosH5P/ContentFullscreenEditor', {
        component: _index2.default
    });
});

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.createConsumerApi = undefined;

var _createConsumerApi = __webpack_require__(11);

var _createConsumerApi2 = _interopRequireDefault(_createConsumerApi);

var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = (0, _readFromConsumerApi2.default)('manifest');
exports.createConsumerApi = _createConsumerApi2.default;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = createConsumerApi;

var _package = __webpack_require__(12);

var _manifest = __webpack_require__(13);

var _manifest2 = _interopRequireDefault(_manifest);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var createReadOnlyValue = function createReadOnlyValue(value) {
    return {
        value: value,
        writable: false,
        enumerable: false,
        configurable: true
    };
};

function createConsumerApi(manifests, exposureMap) {
    var api = {};

    Object.keys(exposureMap).forEach(function (key) {
        Object.defineProperty(api, key, createReadOnlyValue(exposureMap[key]));
    });

    Object.defineProperty(api, '@manifest', createReadOnlyValue((0, _manifest2.default)(manifests)));

    Object.defineProperty(window, '@Neos:HostPluginAPI', createReadOnlyValue(api));
    Object.defineProperty(window['@Neos:HostPluginAPI'], 'VERSION', createReadOnlyValue(_package.version));
}

/***/ }),
/* 12 */
/***/ (function(module, exports) {

module.exports = {"name":"@neos-project/neos-ui-extensibility","version":"1.3.2","description":"Extensibility mechanisms for the Neos CMS UI","main":"./src/index.js","scripts":{"prebuild":"check-dependencies && yarn clean","test":"yarn jest -- -w 2 --coverage","test:watch":"yarn jest -- --watch","build":"exit 0","build:watch":"exit 0","clean":"rimraf ./lib ./dist","lint":"eslint src","jest":"NODE_ENV=test jest"},"devDependencies":{"@neos-project/babel-preset-neos-ui":"1.3.2","@neos-project/jest-preset-neos-ui":"1.3.2"},"dependencies":{"@neos-project/build-essentials":"1.3.2","@neos-project/positional-array-sorter":"1.3.2","babel-core":"^6.13.2","babel-eslint":"^7.1.1","babel-loader":"^7.1.2","babel-plugin-transform-decorators-legacy":"^1.3.4","babel-plugin-transform-object-rest-spread":"^6.20.1","babel-plugin-webpack-alias":"^2.1.1","babel-preset-es2015":"^6.13.2","babel-preset-react":"^6.3.13","babel-preset-stage-0":"^6.3.13","chalk":"^1.1.3","css-loader":"^0.28.4","file-loader":"^1.1.5","json-loader":"^0.5.4","postcss-loader":"^2.0.10","react-dev-utils":"^0.5.0","style-loader":"^0.21.0"},"bin":{"neos-react-scripts":"./bin/neos-react-scripts.js"},"jest":{"preset":"@neos-project/jest-preset-neos-ui"}}

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

exports.default = function (manifests) {
    return function manifest(identifier, options, bootstrap) {
        manifests.push(_defineProperty({}, identifier, {
            options: options,
            bootstrap: bootstrap
        }));
    };
};

/***/ }),
/* 14 */,
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _class, _temp;

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(2);

var _propTypes2 = _interopRequireDefault(_propTypes);

var _neosUiDecorators = __webpack_require__(3);

var _reactUiComponents = __webpack_require__(4);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var ContentFullscreenEditor = (_temp = _class = function (_PureComponent) {
    _inherits(ContentFullscreenEditor, _PureComponent);

    function ContentFullscreenEditor() {
        _classCallCheck(this, ContentFullscreenEditor);

        return _possibleConstructorReturn(this, (ContentFullscreenEditor.__proto__ || Object.getPrototypeOf(ContentFullscreenEditor)).apply(this, arguments));
    }

    _createClass(ContentFullscreenEditor, [{
        key: 'render',
        value: function render() {
            window.NeosH5PBrowserCallbacks = {
                contentPicked: this.props.onContentPicked,
                currentContent: this.props.currentContent
            };
            return _react2.default.createElement('iframe', {
                src: '/neosh5p/contentfullscreeneditor/' + this.props.action + (this.props.currentContent && !this.props.doNotAppendToQuery ? '/' + this.props.currentContent.persistenceObjectIdentifier : ''),
                style: {
                    position: 'absolute',
                    width: '100%',
                    height: '100%',
                    border: '0'
                } });
        }
    }]);

    return ContentFullscreenEditor;
}(_react.PureComponent), _class.propTypes = {
    action: _propTypes2.default.string.isRequired,
    onContentPicked: _propTypes2.default.func.isRequired,
    currentContent: _propTypes2.default.shape({
        persistenceObjectIdentifier: _propTypes2.default.string.isRequired,
        contentId: _propTypes2.default.string.isRequired,
        title: _propTypes2.default.string.isRequired
    }),
    doNotAppendToQuery: _propTypes2.default.boolean
}, _temp);
exports.default = ContentFullscreenEditor;

/***/ }),
/* 16 */,
/* 17 */,
/* 18 */,
/* 19 */,
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _dec, _class, _class2, _temp;

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(2);

var _propTypes2 = _interopRequireDefault(_propTypes);

var _neosUiDecorators = __webpack_require__(3);

var _reactUiComponents = __webpack_require__(4);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var ContentPickerEditor = (_dec = (0, _neosUiDecorators.neos)(function (globalRegistry) {
    return {
        secondaryEditorsRegistry: globalRegistry.get('inspector').get('secondaryEditors')
    };
}), _dec(_class = (_temp = _class2 = function (_PureComponent) {
    _inherits(ContentPickerEditor, _PureComponent);

    function ContentPickerEditor(props) {
        _classCallCheck(this, ContentPickerEditor);

        var _this = _possibleConstructorReturn(this, (ContentPickerEditor.__proto__ || Object.getPrototypeOf(ContentPickerEditor)).call(this, props));

        _this.fetchContentDetails = function (contentId) {
            return fetch('/neos/service/data-source/sandstorm-neosh5p-content?contentId=' + contentId, { credentials: 'same-origin' }).then(function (response) {
                return response.json();
            }).then(function (json) {
                return _this.setState(json);
            });
        };

        _this.onContentPicked = function (content) {
            _this.setState(content);
            _this.props.commit(content.contentId);
            // hide fullscreen editor
            _this.props.renderSecondaryInspector('H5P_CONTENT_FULLSCREEN_EDITOR');
        };

        _this.handleDisplayContent = function () {
            var _this$props$secondary = _this.props.secondaryEditorsRegistry.get('Sandstorm.NeosH5P/ContentFullscreenEditor'),
                ContentFullscreenEditor = _this$props$secondary.component;

            _this.props.renderSecondaryInspector('H5P_CONTENT_FULLSCREEN_EDITOR', function () {
                return _react2.default.createElement(ContentFullscreenEditor, {
                    action: 'display',
                    currentContent: _this.state,
                    onContentPicked: _this.onContentPicked });
            });
        };

        _this.handleNewContent = function () {
            var _this$props$secondary2 = _this.props.secondaryEditorsRegistry.get('Sandstorm.NeosH5P/ContentFullscreenEditor'),
                ContentFullscreenEditor = _this$props$secondary2.component;

            _this.props.renderSecondaryInspector('H5P_CONTENT_FULLSCREEN_EDITOR', function () {
                return _react2.default.createElement(ContentFullscreenEditor, {
                    action: 'new',
                    currentContent: _this.state,
                    doNotAppendToQuery: true,
                    onContentPicked: _this.onContentPicked });
            });
        };

        _this.handleChooseContent = function () {
            var _this$props$secondary3 = _this.props.secondaryEditorsRegistry.get('Sandstorm.NeosH5P/ContentFullscreenEditor'),
                ContentFullscreenEditor = _this$props$secondary3.component;

            _this.props.renderSecondaryInspector('H5P_CONTENT_FULLSCREEN_EDITOR', function () {
                return _react2.default.createElement(ContentFullscreenEditor, {
                    action: 'index',
                    currentContent: _this.state,
                    doNotAppendToQuery: true,
                    onContentPicked: _this.onContentPicked });
            });
        };

        _this.state = {
            persistenceObjectIdentifier: null,
            contentId: null,
            title: null
        };
        if (_this.props.value) {
            _this.fetchContentDetails(_this.props.value);
        }
        return _this;
    }

    _createClass(ContentPickerEditor, [{
        key: 'render',
        value: function render() {
            return _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(
                    'p',
                    null,
                    _react2.default.createElement(
                        'strong',
                        null,
                        this.state.title ? this.state.title : 'No Content selected.'
                    )
                ),
                _react2.default.createElement(
                    'div',
                    null,
                    _react2.default.createElement(
                        _reactUiComponents.Button,
                        { style: 'lighter', onClick: this.handleNewContent },
                        'New'
                    ),
                    _react2.default.createElement(
                        _reactUiComponents.Button,
                        { style: 'lighter', onClick: this.handleChooseContent },
                        'Choose'
                    ),
                    _react2.default.createElement(
                        _reactUiComponents.Button,
                        { style: 'lighter', isDisabled: !this.props.value, onClick: this.handleDisplayContent },
                        'Edit'
                    )
                )
            );
        }
    }]);

    return ContentPickerEditor;
}(_react.PureComponent), _class2.propTypes = {
    value: _propTypes2.default.string,
    commit: _propTypes2.default.func.isRequired,
    secondaryEditorsRegistry: _propTypes2.default.object.isRequired,
    renderSecondaryInspector: _propTypes2.default.func.isRequired
}, _temp)) || _class);
exports.default = ContentPickerEditor;

/***/ })
/******/ ]);
//# sourceMappingURL=Plugin.js.map