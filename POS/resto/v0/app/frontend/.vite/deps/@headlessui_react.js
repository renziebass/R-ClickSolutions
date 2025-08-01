import {
  clsx_default
} from "./chunk-2KHBIA62.js";
import {
  require_react_dom
} from "./chunk-WEJJOBXA.js";
import {
  __commonJS,
  __toESM,
  require_react
} from "./chunk-VCDLJVZS.js";

// node_modules/use-sync-external-store/cjs/use-sync-external-store-with-selector.development.js
var require_use_sync_external_store_with_selector_development = __commonJS({
  "node_modules/use-sync-external-store/cjs/use-sync-external-store-with-selector.development.js"(exports) {
    "use strict";
    (function() {
      function is(x11, y8) {
        return x11 === y8 && (0 !== x11 || 1 / x11 === 1 / y8) || x11 !== x11 && y8 !== y8;
      }
      "undefined" !== typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ && "function" === typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.registerInternalModuleStart && __REACT_DEVTOOLS_GLOBAL_HOOK__.registerInternalModuleStart(Error());
      var React4 = require_react(), objectIs = "function" === typeof Object.is ? Object.is : is, useSyncExternalStore = React4.useSyncExternalStore, useRef5 = React4.useRef, useEffect7 = React4.useEffect, useMemo3 = React4.useMemo, useDebugValue = React4.useDebugValue;
      exports.useSyncExternalStoreWithSelector = function(subscribe, getSnapshot, getServerSnapshot, selector, isEqual) {
        var instRef = useRef5(null);
        if (null === instRef.current) {
          var inst = { hasValue: false, value: null };
          instRef.current = inst;
        } else inst = instRef.current;
        instRef = useMemo3(
          function() {
            function memoizedSelector(nextSnapshot) {
              if (!hasMemo) {
                hasMemo = true;
                memoizedSnapshot = nextSnapshot;
                nextSnapshot = selector(nextSnapshot);
                if (void 0 !== isEqual && inst.hasValue) {
                  var currentSelection = inst.value;
                  if (isEqual(currentSelection, nextSnapshot))
                    return memoizedSelection = currentSelection;
                }
                return memoizedSelection = nextSnapshot;
              }
              currentSelection = memoizedSelection;
              if (objectIs(memoizedSnapshot, nextSnapshot))
                return currentSelection;
              var nextSelection = selector(nextSnapshot);
              if (void 0 !== isEqual && isEqual(currentSelection, nextSelection))
                return memoizedSnapshot = nextSnapshot, currentSelection;
              memoizedSnapshot = nextSnapshot;
              return memoizedSelection = nextSelection;
            }
            var hasMemo = false, memoizedSnapshot, memoizedSelection, maybeGetServerSnapshot = void 0 === getServerSnapshot ? null : getServerSnapshot;
            return [
              function() {
                return memoizedSelector(getSnapshot());
              },
              null === maybeGetServerSnapshot ? void 0 : function() {
                return memoizedSelector(maybeGetServerSnapshot());
              }
            ];
          },
          [getSnapshot, getServerSnapshot, selector, isEqual]
        );
        var value = useSyncExternalStore(subscribe, instRef[0], instRef[1]);
        useEffect7(
          function() {
            inst.hasValue = true;
            inst.value = value;
          },
          [value]
        );
        useDebugValue(value);
        return value;
      };
      "undefined" !== typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ && "function" === typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.registerInternalModuleStop && __REACT_DEVTOOLS_GLOBAL_HOOK__.registerInternalModuleStop(Error());
    })();
  }
});

// node_modules/use-sync-external-store/with-selector.js
var require_with_selector = __commonJS({
  "node_modules/use-sync-external-store/with-selector.js"(exports, module) {
    "use strict";
    if (false) {
      module.exports = null;
    } else {
      module.exports = require_use_sync_external_store_with_selector_development();
    }
  }
});

// node_modules/@react-aria/utils/dist/useLayoutEffect.mjs
var import_react = __toESM(require_react(), 1);
var $f0a04ccd8dbdd83b$export$e5c5a5f917a5871c = typeof document !== "undefined" ? (0, import_react.default).useLayoutEffect : () => {
};

// node_modules/@react-aria/utils/dist/useEffectEvent.mjs
var import_react2 = __toESM(require_react(), 1);
function $8ae05eaa5c114e9c$export$7f54fc3180508a52(fn) {
  const ref = (0, import_react2.useRef)(null);
  (0, $f0a04ccd8dbdd83b$export$e5c5a5f917a5871c)(() => {
    ref.current = fn;
  }, [
    fn
  ]);
  return (0, import_react2.useCallback)((...args) => {
    const f22 = ref.current;
    return f22 === null || f22 === void 0 ? void 0 : f22(...args);
  }, []);
}

// node_modules/@react-aria/utils/dist/useValueEffect.mjs
var import_react3 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useId.mjs
var import_react5 = __toESM(require_react(), 1);

// node_modules/@react-aria/ssr/dist/SSRProvider.mjs
var import_react4 = __toESM(require_react(), 1);
var $b5e257d569688ac6$var$defaultContext = {
  prefix: String(Math.round(Math.random() * 1e10)),
  current: 0
};
var $b5e257d569688ac6$var$SSRContext = (0, import_react4.default).createContext($b5e257d569688ac6$var$defaultContext);
var $b5e257d569688ac6$var$IsSSRContext = (0, import_react4.default).createContext(false);
var $b5e257d569688ac6$var$canUseDOM = Boolean(typeof window !== "undefined" && window.document && window.document.createElement);
var $b5e257d569688ac6$var$componentIds = /* @__PURE__ */ new WeakMap();
function $b5e257d569688ac6$var$useCounter(isDisabled2 = false) {
  let ctx = (0, import_react4.useContext)($b5e257d569688ac6$var$SSRContext);
  let ref = (0, import_react4.useRef)(null);
  if (ref.current === null && !isDisabled2) {
    var _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED_ReactCurrentOwner, _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED;
    let currentOwner = (_React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = (0, import_react4.default).__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED) === null || _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED === void 0 ? void 0 : (_React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED_ReactCurrentOwner = _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner) === null || _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED_ReactCurrentOwner === void 0 ? void 0 : _React___SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED_ReactCurrentOwner.current;
    if (currentOwner) {
      let prevComponentValue = $b5e257d569688ac6$var$componentIds.get(currentOwner);
      if (prevComponentValue == null)
        $b5e257d569688ac6$var$componentIds.set(currentOwner, {
          id: ctx.current,
          state: currentOwner.memoizedState
        });
      else if (currentOwner.memoizedState !== prevComponentValue.state) {
        ctx.current = prevComponentValue.id;
        $b5e257d569688ac6$var$componentIds.delete(currentOwner);
      }
    }
    ref.current = ++ctx.current;
  }
  return ref.current;
}
function $b5e257d569688ac6$var$useLegacySSRSafeId(defaultId) {
  let ctx = (0, import_react4.useContext)($b5e257d569688ac6$var$SSRContext);
  if (ctx === $b5e257d569688ac6$var$defaultContext && !$b5e257d569688ac6$var$canUseDOM && true) console.warn("When server rendering, you must wrap your application in an <SSRProvider> to ensure consistent ids are generated between the client and server.");
  let counter = $b5e257d569688ac6$var$useCounter(!!defaultId);
  let prefix = ctx === $b5e257d569688ac6$var$defaultContext && false ? "react-aria" : `react-aria${ctx.prefix}`;
  return defaultId || `${prefix}-${counter}`;
}
function $b5e257d569688ac6$var$useModernSSRSafeId(defaultId) {
  let id = (0, import_react4.default).useId();
  let [didSSR] = (0, import_react4.useState)($b5e257d569688ac6$export$535bd6ca7f90a273());
  let prefix = didSSR || false ? "react-aria" : `react-aria${$b5e257d569688ac6$var$defaultContext.prefix}`;
  return defaultId || `${prefix}-${id}`;
}
var $b5e257d569688ac6$export$619500959fc48b26 = typeof (0, import_react4.default)["useId"] === "function" ? $b5e257d569688ac6$var$useModernSSRSafeId : $b5e257d569688ac6$var$useLegacySSRSafeId;
function $b5e257d569688ac6$var$getSnapshot() {
  return false;
}
function $b5e257d569688ac6$var$getServerSnapshot() {
  return true;
}
function $b5e257d569688ac6$var$subscribe(onStoreChange) {
  return () => {
  };
}
function $b5e257d569688ac6$export$535bd6ca7f90a273() {
  if (typeof (0, import_react4.default)["useSyncExternalStore"] === "function") return (0, import_react4.default)["useSyncExternalStore"]($b5e257d569688ac6$var$subscribe, $b5e257d569688ac6$var$getSnapshot, $b5e257d569688ac6$var$getServerSnapshot);
  return (0, import_react4.useContext)($b5e257d569688ac6$var$IsSSRContext);
}

// node_modules/@react-aria/utils/dist/useId.mjs
var $bdb11010cef70236$var$canUseDOM = Boolean(typeof window !== "undefined" && window.document && window.document.createElement);
var $bdb11010cef70236$export$d41a04c74483c6ef = /* @__PURE__ */ new Map();
var $bdb11010cef70236$var$registry;
if (typeof FinalizationRegistry !== "undefined") $bdb11010cef70236$var$registry = new FinalizationRegistry((heldValue) => {
  $bdb11010cef70236$export$d41a04c74483c6ef.delete(heldValue);
});
function $bdb11010cef70236$export$cd8c9cb68f842629(idA, idB) {
  if (idA === idB) return idA;
  let setIdsA = $bdb11010cef70236$export$d41a04c74483c6ef.get(idA);
  if (setIdsA) {
    setIdsA.forEach((ref) => ref.current = idB);
    return idB;
  }
  let setIdsB = $bdb11010cef70236$export$d41a04c74483c6ef.get(idB);
  if (setIdsB) {
    setIdsB.forEach((ref) => ref.current = idA);
    return idA;
  }
  return idB;
}

// node_modules/@react-aria/utils/dist/chain.mjs
function $ff5963eb1fccf552$export$e08e3b67e392101e(...callbacks) {
  return (...args) => {
    for (let callback of callbacks) if (typeof callback === "function") callback(...args);
  };
}

// node_modules/@react-aria/utils/dist/domHelpers.mjs
var $431fbd86ca7dc216$export$b204af158042fbac = (el) => {
  var _el_ownerDocument;
  return (_el_ownerDocument = el === null || el === void 0 ? void 0 : el.ownerDocument) !== null && _el_ownerDocument !== void 0 ? _el_ownerDocument : document;
};
var $431fbd86ca7dc216$export$f21a1ffae260145a = (el) => {
  if (el && "window" in el && el.window === el) return el;
  const doc = $431fbd86ca7dc216$export$b204af158042fbac(el);
  return doc.defaultView || window;
};
function $431fbd86ca7dc216$var$isNode(value) {
  return value !== null && typeof value === "object" && "nodeType" in value && typeof value.nodeType === "number";
}
function $431fbd86ca7dc216$export$af51f0f06c0f328a(node) {
  return $431fbd86ca7dc216$var$isNode(node) && node.nodeType === Node.DOCUMENT_FRAGMENT_NODE && "host" in node;
}

// node_modules/@react-stately/flags/dist/import.mjs
var $f4e2df6bd15f8569$var$_shadowDOM = false;
function $f4e2df6bd15f8569$export$98658e8c59125e6a() {
  return $f4e2df6bd15f8569$var$_shadowDOM;
}

// node_modules/@react-aria/utils/dist/DOMFunctions.mjs
function $d4ee10de306f2510$export$4282f70798064fe0(node, otherNode) {
  if (!(0, $f4e2df6bd15f8569$export$98658e8c59125e6a)()) return otherNode && node ? node.contains(otherNode) : false;
  if (!node || !otherNode) return false;
  let currentNode = otherNode;
  while (currentNode !== null) {
    if (currentNode === node) return true;
    if (currentNode.tagName === "SLOT" && currentNode.assignedSlot)
      currentNode = currentNode.assignedSlot.parentNode;
    else if ((0, $431fbd86ca7dc216$export$af51f0f06c0f328a)(currentNode))
      currentNode = currentNode.host;
    else currentNode = currentNode.parentNode;
  }
  return false;
}
var $d4ee10de306f2510$export$cd4e5573fbe2b576 = (doc = document) => {
  var _activeElement_shadowRoot;
  if (!(0, $f4e2df6bd15f8569$export$98658e8c59125e6a)()) return doc.activeElement;
  let activeElement2 = doc.activeElement;
  while (activeElement2 && "shadowRoot" in activeElement2 && ((_activeElement_shadowRoot = activeElement2.shadowRoot) === null || _activeElement_shadowRoot === void 0 ? void 0 : _activeElement_shadowRoot.activeElement)) activeElement2 = activeElement2.shadowRoot.activeElement;
  return activeElement2;
};
function $d4ee10de306f2510$export$e58f029f0fbfdb29(event) {
  if ((0, $f4e2df6bd15f8569$export$98658e8c59125e6a)() && event.target.shadowRoot) {
    if (event.composedPath) return event.composedPath()[0];
  }
  return event.target;
}

// node_modules/@react-aria/utils/dist/mergeProps.mjs
function $3ef42575df84b30b$export$9d1611c77c2fe928(...args) {
  let result = {
    ...args[0]
  };
  for (let i19 = 1; i19 < args.length; i19++) {
    let props = args[i19];
    for (let key in props) {
      let a25 = result[key];
      let b11 = props[key];
      if (typeof a25 === "function" && typeof b11 === "function" && // This is a lot faster than a regex.
      key[0] === "o" && key[1] === "n" && key.charCodeAt(2) >= /* 'A' */
      65 && key.charCodeAt(2) <= /* 'Z' */
      90) result[key] = (0, $ff5963eb1fccf552$export$e08e3b67e392101e)(a25, b11);
      else if ((key === "className" || key === "UNSAFE_className") && typeof a25 === "string" && typeof b11 === "string") result[key] = (0, clsx_default)(a25, b11);
      else if (key === "id" && a25 && b11) result.id = (0, $bdb11010cef70236$export$cd8c9cb68f842629)(a25, b11);
      else result[key] = b11 !== void 0 ? b11 : a25;
    }
  }
  return result;
}

// node_modules/@react-aria/utils/dist/mergeRefs.mjs
function $5dc95899b306f630$export$c9058316764c140e(...refs) {
  if (refs.length === 1 && refs[0]) return refs[0];
  return (value) => {
    for (let ref of refs) {
      if (typeof ref === "function") ref(value);
      else if (ref != null) ref.current = value;
    }
  };
}

// node_modules/@react-aria/utils/dist/focusWithoutScrolling.mjs
function $7215afc6de606d6b$export$de79e2c695e052f3(element) {
  if ($7215afc6de606d6b$var$supportsPreventScroll()) element.focus({
    preventScroll: true
  });
  else {
    let scrollableElements = $7215afc6de606d6b$var$getScrollableElements(element);
    element.focus();
    $7215afc6de606d6b$var$restoreScrollPosition(scrollableElements);
  }
}
var $7215afc6de606d6b$var$supportsPreventScrollCached = null;
function $7215afc6de606d6b$var$supportsPreventScroll() {
  if ($7215afc6de606d6b$var$supportsPreventScrollCached == null) {
    $7215afc6de606d6b$var$supportsPreventScrollCached = false;
    try {
      let focusElem = document.createElement("div");
      focusElem.focus({
        get preventScroll() {
          $7215afc6de606d6b$var$supportsPreventScrollCached = true;
          return true;
        }
      });
    } catch {
    }
  }
  return $7215afc6de606d6b$var$supportsPreventScrollCached;
}
function $7215afc6de606d6b$var$getScrollableElements(element) {
  let parent = element.parentNode;
  let scrollableElements = [];
  let rootScrollingElement = document.scrollingElement || document.documentElement;
  while (parent instanceof HTMLElement && parent !== rootScrollingElement) {
    if (parent.offsetHeight < parent.scrollHeight || parent.offsetWidth < parent.scrollWidth) scrollableElements.push({
      element: parent,
      scrollTop: parent.scrollTop,
      scrollLeft: parent.scrollLeft
    });
    parent = parent.parentNode;
  }
  if (rootScrollingElement instanceof HTMLElement) scrollableElements.push({
    element: rootScrollingElement,
    scrollTop: rootScrollingElement.scrollTop,
    scrollLeft: rootScrollingElement.scrollLeft
  });
  return scrollableElements;
}
function $7215afc6de606d6b$var$restoreScrollPosition(scrollableElements) {
  for (let { element, scrollTop, scrollLeft } of scrollableElements) {
    element.scrollTop = scrollTop;
    element.scrollLeft = scrollLeft;
  }
}

// node_modules/@react-aria/utils/dist/platform.mjs
function $c87311424ea30a05$var$testUserAgent(re6) {
  var _window_navigator_userAgentData;
  if (typeof window === "undefined" || window.navigator == null) return false;
  return ((_window_navigator_userAgentData = window.navigator["userAgentData"]) === null || _window_navigator_userAgentData === void 0 ? void 0 : _window_navigator_userAgentData.brands.some((brand) => re6.test(brand.brand))) || re6.test(window.navigator.userAgent);
}
function $c87311424ea30a05$var$testPlatform(re6) {
  var _window_navigator_userAgentData;
  return typeof window !== "undefined" && window.navigator != null ? re6.test(((_window_navigator_userAgentData = window.navigator["userAgentData"]) === null || _window_navigator_userAgentData === void 0 ? void 0 : _window_navigator_userAgentData.platform) || window.navigator.platform) : false;
}
function $c87311424ea30a05$var$cached(fn) {
  if (false) return fn;
  let res = null;
  return () => {
    if (res == null) res = fn();
    return res;
  };
}
var $c87311424ea30a05$export$9ac100e40613ea10 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testPlatform(/^Mac/i);
});
var $c87311424ea30a05$export$186c6964ca17d99 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testPlatform(/^iPhone/i);
});
var $c87311424ea30a05$export$7bef049ce92e4224 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testPlatform(/^iPad/i) || // iPadOS 13 lies and says it's a Mac, but we can distinguish by detecting touch support.
  $c87311424ea30a05$export$9ac100e40613ea10() && navigator.maxTouchPoints > 1;
});
var $c87311424ea30a05$export$fedb369cb70207f1 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$export$186c6964ca17d99() || $c87311424ea30a05$export$7bef049ce92e4224();
});
var $c87311424ea30a05$export$e1865c3bedcd822b = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$export$9ac100e40613ea10() || $c87311424ea30a05$export$fedb369cb70207f1();
});
var $c87311424ea30a05$export$78551043582a6a98 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testUserAgent(/AppleWebKit/i) && !$c87311424ea30a05$export$6446a186d09e379e();
});
var $c87311424ea30a05$export$6446a186d09e379e = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testUserAgent(/Chrome/i);
});
var $c87311424ea30a05$export$a11b0059900ceec8 = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testUserAgent(/Android/i);
});
var $c87311424ea30a05$export$b7d78993b74f766d = $c87311424ea30a05$var$cached(function() {
  return $c87311424ea30a05$var$testUserAgent(/Firefox/i);
});

// node_modules/@react-aria/utils/dist/openLink.mjs
var import_react6 = __toESM(require_react(), 1);
var $ea8dcbcb9ea1b556$var$RouterContext = (0, import_react6.createContext)({
  isNative: true,
  open: $ea8dcbcb9ea1b556$var$openSyntheticLink,
  useHref: (href) => href
});
function $ea8dcbcb9ea1b556$export$95185d699e05d4d7(target, modifiers, setOpening = true) {
  var _window_event_type, _window_event;
  let { metaKey, ctrlKey, altKey, shiftKey } = modifiers;
  if ((0, $c87311424ea30a05$export$b7d78993b74f766d)() && ((_window_event = window.event) === null || _window_event === void 0 ? void 0 : (_window_event_type = _window_event.type) === null || _window_event_type === void 0 ? void 0 : _window_event_type.startsWith("key")) && target.target === "_blank") {
    if ((0, $c87311424ea30a05$export$9ac100e40613ea10)()) metaKey = true;
    else ctrlKey = true;
  }
  let event = (0, $c87311424ea30a05$export$78551043582a6a98)() && (0, $c87311424ea30a05$export$9ac100e40613ea10)() && !(0, $c87311424ea30a05$export$7bef049ce92e4224)() && true ? new KeyboardEvent("keydown", {
    keyIdentifier: "Enter",
    metaKey,
    ctrlKey,
    altKey,
    shiftKey
  }) : new MouseEvent("click", {
    metaKey,
    ctrlKey,
    altKey,
    shiftKey,
    bubbles: true,
    cancelable: true
  });
  $ea8dcbcb9ea1b556$export$95185d699e05d4d7.isOpening = setOpening;
  (0, $7215afc6de606d6b$export$de79e2c695e052f3)(target);
  target.dispatchEvent(event);
  $ea8dcbcb9ea1b556$export$95185d699e05d4d7.isOpening = false;
}
$ea8dcbcb9ea1b556$export$95185d699e05d4d7.isOpening = false;
function $ea8dcbcb9ea1b556$var$getSyntheticLink(target, open) {
  if (target instanceof HTMLAnchorElement) open(target);
  else if (target.hasAttribute("data-href")) {
    let link = document.createElement("a");
    link.href = target.getAttribute("data-href");
    if (target.hasAttribute("data-target")) link.target = target.getAttribute("data-target");
    if (target.hasAttribute("data-rel")) link.rel = target.getAttribute("data-rel");
    if (target.hasAttribute("data-download")) link.download = target.getAttribute("data-download");
    if (target.hasAttribute("data-ping")) link.ping = target.getAttribute("data-ping");
    if (target.hasAttribute("data-referrer-policy")) link.referrerPolicy = target.getAttribute("data-referrer-policy");
    target.appendChild(link);
    open(link);
    target.removeChild(link);
  }
}
function $ea8dcbcb9ea1b556$var$openSyntheticLink(target, modifiers) {
  $ea8dcbcb9ea1b556$var$getSyntheticLink(target, (link) => $ea8dcbcb9ea1b556$export$95185d699e05d4d7(link, modifiers));
}

// node_modules/@react-aria/utils/dist/runAfterTransition.mjs
var $bbed8b41f857bcc0$var$transitionsByElement = /* @__PURE__ */ new Map();
var $bbed8b41f857bcc0$var$transitionCallbacks = /* @__PURE__ */ new Set();
function $bbed8b41f857bcc0$var$setupGlobalEvents() {
  if (typeof window === "undefined") return;
  function isTransitionEvent(event) {
    return "propertyName" in event;
  }
  let onTransitionStart = (e8) => {
    if (!isTransitionEvent(e8) || !e8.target) return;
    let transitions = $bbed8b41f857bcc0$var$transitionsByElement.get(e8.target);
    if (!transitions) {
      transitions = /* @__PURE__ */ new Set();
      $bbed8b41f857bcc0$var$transitionsByElement.set(e8.target, transitions);
      e8.target.addEventListener("transitioncancel", onTransitionEnd, {
        once: true
      });
    }
    transitions.add(e8.propertyName);
  };
  let onTransitionEnd = (e8) => {
    if (!isTransitionEvent(e8) || !e8.target) return;
    let properties = $bbed8b41f857bcc0$var$transitionsByElement.get(e8.target);
    if (!properties) return;
    properties.delete(e8.propertyName);
    if (properties.size === 0) {
      e8.target.removeEventListener("transitioncancel", onTransitionEnd);
      $bbed8b41f857bcc0$var$transitionsByElement.delete(e8.target);
    }
    if ($bbed8b41f857bcc0$var$transitionsByElement.size === 0) {
      for (let cb of $bbed8b41f857bcc0$var$transitionCallbacks) cb();
      $bbed8b41f857bcc0$var$transitionCallbacks.clear();
    }
  };
  document.body.addEventListener("transitionrun", onTransitionStart);
  document.body.addEventListener("transitionend", onTransitionEnd);
}
if (typeof document !== "undefined") {
  if (document.readyState !== "loading") $bbed8b41f857bcc0$var$setupGlobalEvents();
  else document.addEventListener("DOMContentLoaded", $bbed8b41f857bcc0$var$setupGlobalEvents);
}
function $bbed8b41f857bcc0$export$24490316f764c430(fn) {
  requestAnimationFrame(() => {
    if ($bbed8b41f857bcc0$var$transitionsByElement.size === 0) fn();
    else $bbed8b41f857bcc0$var$transitionCallbacks.add(fn);
  });
}

// node_modules/@react-aria/utils/dist/useDrag1D.mjs
var import_react7 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useGlobalListeners.mjs
var import_react8 = __toESM(require_react(), 1);
function $03deb23ff14920c4$export$4eaf04e54aa8eed6() {
  let globalListeners = (0, import_react8.useRef)(/* @__PURE__ */ new Map());
  let addGlobalListener = (0, import_react8.useCallback)((eventTarget, type, listener, options) => {
    let fn = (options === null || options === void 0 ? void 0 : options.once) ? (...args) => {
      globalListeners.current.delete(listener);
      listener(...args);
    } : listener;
    globalListeners.current.set(listener, {
      type,
      eventTarget,
      fn,
      options
    });
    eventTarget.addEventListener(type, fn, options);
  }, []);
  let removeGlobalListener = (0, import_react8.useCallback)((eventTarget, type, listener, options) => {
    var _globalListeners_current_get;
    let fn = ((_globalListeners_current_get = globalListeners.current.get(listener)) === null || _globalListeners_current_get === void 0 ? void 0 : _globalListeners_current_get.fn) || listener;
    eventTarget.removeEventListener(type, fn, options);
    globalListeners.current.delete(listener);
  }, []);
  let removeAllGlobalListeners = (0, import_react8.useCallback)(() => {
    globalListeners.current.forEach((value, key) => {
      removeGlobalListener(value.eventTarget, value.type, key, value.options);
    });
  }, [
    removeGlobalListener
  ]);
  (0, import_react8.useEffect)(() => {
    return removeAllGlobalListeners;
  }, [
    removeAllGlobalListeners
  ]);
  return {
    addGlobalListener,
    removeGlobalListener,
    removeAllGlobalListeners
  };
}

// node_modules/@react-aria/utils/dist/useObjectRef.mjs
var import_react9 = __toESM(require_react(), 1);
function $df56164dff5785e2$export$4338b53315abf666(forwardedRef) {
  const objRef = (0, import_react9.useRef)(null);
  return (0, import_react9.useMemo)(() => ({
    get current() {
      return objRef.current;
    },
    set current(value) {
      objRef.current = value;
      if (typeof forwardedRef === "function") forwardedRef(value);
      else if (forwardedRef) forwardedRef.current = value;
    }
  }), [
    forwardedRef
  ]);
}

// node_modules/@react-aria/utils/dist/useUpdateEffect.mjs
var import_react10 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useUpdateLayoutEffect.mjs
var import_react11 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useResizeObserver.mjs
var import_react12 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useSyncRef.mjs
function $e7801be82b4b2a53$export$4debdb1a3f0fa79e(context, ref) {
  (0, $f0a04ccd8dbdd83b$export$e5c5a5f917a5871c)(() => {
    if (context && context.ref && ref) {
      context.ref.current = ref.current;
      return () => {
        if (context.ref) context.ref.current = null;
      };
    }
  });
}

// node_modules/@react-aria/utils/dist/useViewportSize.mjs
var import_react13 = __toESM(require_react(), 1);
var $5df64b3807dc15ee$var$visualViewport = typeof document !== "undefined" && window.visualViewport;

// node_modules/@react-aria/utils/dist/useDescription.mjs
var import_react14 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useEvent.mjs
var import_react15 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/isVirtualEvent.mjs
function $6a7db85432448f7f$export$60278871457622de(event) {
  if (event.mozInputSource === 0 && event.isTrusted) return true;
  if ((0, $c87311424ea30a05$export$a11b0059900ceec8)() && event.pointerType) return event.type === "click" && event.buttons === 1;
  return event.detail === 0 && !event.pointerType;
}
function $6a7db85432448f7f$export$29bf1b5f2c56cf63(event) {
  return !(0, $c87311424ea30a05$export$a11b0059900ceec8)() && event.width === 0 && event.height === 0 || event.width === 1 && event.height === 1 && event.pressure === 0 && event.detail === 0 && event.pointerType === "mouse";
}

// node_modules/@react-aria/utils/dist/useDeepMemo.mjs
var import_react16 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useFormReset.mjs
var import_react17 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/useLoadMore.mjs
var import_react18 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/inertValue.mjs
var import_react19 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/animation.mjs
var import_react_dom = __toESM(require_react_dom(), 1);
var import_react20 = __toESM(require_react(), 1);

// node_modules/@react-aria/utils/dist/isFocusable.mjs
var $b4b717babfbb907b$var$focusableElements = [
  "input:not([disabled]):not([type=hidden])",
  "select:not([disabled])",
  "textarea:not([disabled])",
  "button:not([disabled])",
  "a[href]",
  "area[href]",
  "summary",
  "iframe",
  "object",
  "embed",
  "audio[controls]",
  "video[controls]",
  '[contenteditable]:not([contenteditable^="false"])'
];
var $b4b717babfbb907b$var$FOCUSABLE_ELEMENT_SELECTOR = $b4b717babfbb907b$var$focusableElements.join(":not([hidden]),") + ",[tabindex]:not([disabled]):not([hidden])";
$b4b717babfbb907b$var$focusableElements.push('[tabindex]:not([tabindex="-1"]):not([disabled])');
var $b4b717babfbb907b$var$TABBABLE_ELEMENT_SELECTOR = $b4b717babfbb907b$var$focusableElements.join(':not([hidden]):not([tabindex="-1"]),');
function $b4b717babfbb907b$export$4c063cf1350e6fed(element) {
  return element.matches($b4b717babfbb907b$var$FOCUSABLE_ELEMENT_SELECTOR);
}

// node_modules/@react-stately/utils/dist/useControlledState.mjs
var import_react21 = __toESM(require_react(), 1);

// node_modules/@react-aria/interactions/dist/utils.mjs
var import_react22 = __toESM(require_react(), 1);
function $8a9cb279dc87e130$export$525bc4921d56d4a(nativeEvent) {
  let event = nativeEvent;
  event.nativeEvent = nativeEvent;
  event.isDefaultPrevented = () => event.defaultPrevented;
  event.isPropagationStopped = () => event.cancelBubble;
  event.persist = () => {
  };
  return event;
}
function $8a9cb279dc87e130$export$c2b7abe5d61ec696(event, target) {
  Object.defineProperty(event, "target", {
    value: target
  });
  Object.defineProperty(event, "currentTarget", {
    value: target
  });
}
function $8a9cb279dc87e130$export$715c682d09d639cc(onBlur) {
  let stateRef = (0, import_react22.useRef)({
    isFocused: false,
    observer: null
  });
  (0, $f0a04ccd8dbdd83b$export$e5c5a5f917a5871c)(() => {
    const state = stateRef.current;
    return () => {
      if (state.observer) {
        state.observer.disconnect();
        state.observer = null;
      }
    };
  }, []);
  let dispatchBlur = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((e8) => {
    onBlur === null || onBlur === void 0 ? void 0 : onBlur(e8);
  });
  return (0, import_react22.useCallback)((e8) => {
    if (e8.target instanceof HTMLButtonElement || e8.target instanceof HTMLInputElement || e8.target instanceof HTMLTextAreaElement || e8.target instanceof HTMLSelectElement) {
      stateRef.current.isFocused = true;
      let target = e8.target;
      let onBlurHandler = (e9) => {
        stateRef.current.isFocused = false;
        if (target.disabled) {
          let event = $8a9cb279dc87e130$export$525bc4921d56d4a(e9);
          dispatchBlur(event);
        }
        if (stateRef.current.observer) {
          stateRef.current.observer.disconnect();
          stateRef.current.observer = null;
        }
      };
      target.addEventListener("focusout", onBlurHandler, {
        once: true
      });
      stateRef.current.observer = new MutationObserver(() => {
        if (stateRef.current.isFocused && target.disabled) {
          var _stateRef_current_observer;
          (_stateRef_current_observer = stateRef.current.observer) === null || _stateRef_current_observer === void 0 ? void 0 : _stateRef_current_observer.disconnect();
          let relatedTargetEl = target === document.activeElement ? null : document.activeElement;
          target.dispatchEvent(new FocusEvent("blur", {
            relatedTarget: relatedTargetEl
          }));
          target.dispatchEvent(new FocusEvent("focusout", {
            bubbles: true,
            relatedTarget: relatedTargetEl
          }));
        }
      });
      stateRef.current.observer.observe(target, {
        attributes: true,
        attributeFilter: [
          "disabled"
        ]
      });
    }
  }, [
    dispatchBlur
  ]);
}
var $8a9cb279dc87e130$export$fda7da73ab5d4c48 = false;
function $8a9cb279dc87e130$export$cabe61c495ee3649(target) {
  while (target && !(0, $b4b717babfbb907b$export$4c063cf1350e6fed)(target)) target = target.parentElement;
  let window2 = (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(target);
  let activeElement2 = window2.document.activeElement;
  if (!activeElement2 || activeElement2 === target) return;
  $8a9cb279dc87e130$export$fda7da73ab5d4c48 = true;
  let isRefocusing = false;
  let onBlur = (e8) => {
    if (e8.target === activeElement2 || isRefocusing) e8.stopImmediatePropagation();
  };
  let onFocusOut = (e8) => {
    if (e8.target === activeElement2 || isRefocusing) {
      e8.stopImmediatePropagation();
      if (!target && !isRefocusing) {
        isRefocusing = true;
        (0, $7215afc6de606d6b$export$de79e2c695e052f3)(activeElement2);
        cleanup2();
      }
    }
  };
  let onFocus = (e8) => {
    if (e8.target === target || isRefocusing) e8.stopImmediatePropagation();
  };
  let onFocusIn = (e8) => {
    if (e8.target === target || isRefocusing) {
      e8.stopImmediatePropagation();
      if (!isRefocusing) {
        isRefocusing = true;
        (0, $7215afc6de606d6b$export$de79e2c695e052f3)(activeElement2);
        cleanup2();
      }
    }
  };
  window2.addEventListener("blur", onBlur, true);
  window2.addEventListener("focusout", onFocusOut, true);
  window2.addEventListener("focusin", onFocusIn, true);
  window2.addEventListener("focus", onFocus, true);
  let cleanup2 = () => {
    cancelAnimationFrame(raf);
    window2.removeEventListener("blur", onBlur, true);
    window2.removeEventListener("focusout", onFocusOut, true);
    window2.removeEventListener("focusin", onFocusIn, true);
    window2.removeEventListener("focus", onFocus, true);
    $8a9cb279dc87e130$export$fda7da73ab5d4c48 = false;
    isRefocusing = false;
  };
  let raf = requestAnimationFrame(cleanup2);
  return cleanup2;
}

// node_modules/@react-aria/interactions/dist/textSelection.mjs
var $14c0b72509d70225$var$state = "default";
var $14c0b72509d70225$var$savedUserSelect = "";
var $14c0b72509d70225$var$modifiedElementMap = /* @__PURE__ */ new WeakMap();
function $14c0b72509d70225$export$16a4697467175487(target) {
  if ((0, $c87311424ea30a05$export$fedb369cb70207f1)()) {
    if ($14c0b72509d70225$var$state === "default") {
      const documentObject = (0, $431fbd86ca7dc216$export$b204af158042fbac)(target);
      $14c0b72509d70225$var$savedUserSelect = documentObject.documentElement.style.webkitUserSelect;
      documentObject.documentElement.style.webkitUserSelect = "none";
    }
    $14c0b72509d70225$var$state = "disabled";
  } else if (target instanceof HTMLElement || target instanceof SVGElement) {
    let property = "userSelect" in target.style ? "userSelect" : "webkitUserSelect";
    $14c0b72509d70225$var$modifiedElementMap.set(target, target.style[property]);
    target.style[property] = "none";
  }
}
function $14c0b72509d70225$export$b0d6fa1ab32e3295(target) {
  if ((0, $c87311424ea30a05$export$fedb369cb70207f1)()) {
    if ($14c0b72509d70225$var$state !== "disabled") return;
    $14c0b72509d70225$var$state = "restoring";
    setTimeout(() => {
      (0, $bbed8b41f857bcc0$export$24490316f764c430)(() => {
        if ($14c0b72509d70225$var$state === "restoring") {
          const documentObject = (0, $431fbd86ca7dc216$export$b204af158042fbac)(target);
          if (documentObject.documentElement.style.webkitUserSelect === "none") documentObject.documentElement.style.webkitUserSelect = $14c0b72509d70225$var$savedUserSelect || "";
          $14c0b72509d70225$var$savedUserSelect = "";
          $14c0b72509d70225$var$state = "default";
        }
      });
    }, 300);
  } else if (target instanceof HTMLElement || target instanceof SVGElement) {
    if (target && $14c0b72509d70225$var$modifiedElementMap.has(target)) {
      let targetOldUserSelect = $14c0b72509d70225$var$modifiedElementMap.get(target);
      let property = "userSelect" in target.style ? "userSelect" : "webkitUserSelect";
      if (target.style[property] === "none") target.style[property] = targetOldUserSelect;
      if (target.getAttribute("style") === "") target.removeAttribute("style");
      $14c0b72509d70225$var$modifiedElementMap.delete(target);
    }
  }
}

// node_modules/@react-aria/interactions/dist/context.mjs
var import_react23 = __toESM(require_react(), 1);
var $ae1eeba8b9eafd08$export$5165eccb35aaadb5 = (0, import_react23.default).createContext({
  register: () => {
  }
});
$ae1eeba8b9eafd08$export$5165eccb35aaadb5.displayName = "PressResponderContext";

// node_modules/@swc/helpers/esm/_class_apply_descriptor_get.js
function _class_apply_descriptor_get(receiver, descriptor) {
  if (descriptor.get) return descriptor.get.call(receiver);
  return descriptor.value;
}

// node_modules/@swc/helpers/esm/_class_extract_field_descriptor.js
function _class_extract_field_descriptor(receiver, privateMap, action) {
  if (!privateMap.has(receiver)) throw new TypeError("attempted to " + action + " private field on non-instance");
  return privateMap.get(receiver);
}

// node_modules/@swc/helpers/esm/_class_private_field_get.js
function _class_private_field_get(receiver, privateMap) {
  var descriptor = _class_extract_field_descriptor(receiver, privateMap, "get");
  return _class_apply_descriptor_get(receiver, descriptor);
}

// node_modules/@swc/helpers/esm/_check_private_redeclaration.js
function _check_private_redeclaration(obj, privateCollection) {
  if (privateCollection.has(obj)) {
    throw new TypeError("Cannot initialize the same private elements twice on an object");
  }
}

// node_modules/@swc/helpers/esm/_class_private_field_init.js
function _class_private_field_init(obj, privateMap, value) {
  _check_private_redeclaration(obj, privateMap);
  privateMap.set(obj, value);
}

// node_modules/@swc/helpers/esm/_class_apply_descriptor_set.js
function _class_apply_descriptor_set(receiver, descriptor, value) {
  if (descriptor.set) descriptor.set.call(receiver, value);
  else {
    if (!descriptor.writable) {
      throw new TypeError("attempted to set read only private field");
    }
    descriptor.value = value;
  }
}

// node_modules/@swc/helpers/esm/_class_private_field_set.js
function _class_private_field_set(receiver, privateMap, value) {
  var descriptor = _class_extract_field_descriptor(receiver, privateMap, "set");
  _class_apply_descriptor_set(receiver, descriptor, value);
  return value;
}

// node_modules/@react-aria/interactions/dist/usePress.mjs
var import_react_dom2 = __toESM(require_react_dom(), 1);
var import_react24 = __toESM(require_react(), 1);
function $f6c31cce2adf654f$var$usePressResponderContext(props) {
  let context = (0, import_react24.useContext)((0, $ae1eeba8b9eafd08$export$5165eccb35aaadb5));
  if (context) {
    let { register, ...contextProps } = context;
    props = (0, $3ef42575df84b30b$export$9d1611c77c2fe928)(contextProps, props);
    register();
  }
  (0, $e7801be82b4b2a53$export$4debdb1a3f0fa79e)(context, props.ref);
  return props;
}
var $f6c31cce2adf654f$var$_shouldStopPropagation = /* @__PURE__ */ new WeakMap();
var $f6c31cce2adf654f$var$PressEvent = class {
  continuePropagation() {
    (0, _class_private_field_set)(this, $f6c31cce2adf654f$var$_shouldStopPropagation, false);
  }
  get shouldStopPropagation() {
    return (0, _class_private_field_get)(this, $f6c31cce2adf654f$var$_shouldStopPropagation);
  }
  constructor(type, pointerType, originalEvent, state) {
    (0, _class_private_field_init)(this, $f6c31cce2adf654f$var$_shouldStopPropagation, {
      writable: true,
      value: void 0
    });
    (0, _class_private_field_set)(this, $f6c31cce2adf654f$var$_shouldStopPropagation, true);
    var _state_target;
    let currentTarget = (_state_target = state === null || state === void 0 ? void 0 : state.target) !== null && _state_target !== void 0 ? _state_target : originalEvent.currentTarget;
    const rect = currentTarget === null || currentTarget === void 0 ? void 0 : currentTarget.getBoundingClientRect();
    let x11, y8 = 0;
    let clientX, clientY = null;
    if (originalEvent.clientX != null && originalEvent.clientY != null) {
      clientX = originalEvent.clientX;
      clientY = originalEvent.clientY;
    }
    if (rect) {
      if (clientX != null && clientY != null) {
        x11 = clientX - rect.left;
        y8 = clientY - rect.top;
      } else {
        x11 = rect.width / 2;
        y8 = rect.height / 2;
      }
    }
    this.type = type;
    this.pointerType = pointerType;
    this.target = originalEvent.currentTarget;
    this.shiftKey = originalEvent.shiftKey;
    this.metaKey = originalEvent.metaKey;
    this.ctrlKey = originalEvent.ctrlKey;
    this.altKey = originalEvent.altKey;
    this.x = x11;
    this.y = y8;
  }
};
var $f6c31cce2adf654f$var$LINK_CLICKED = Symbol("linkClicked");
function $f6c31cce2adf654f$export$45712eceda6fad21(props) {
  let { onPress, onPressChange, onPressStart, onPressEnd, onPressUp, onClick, isDisabled: isDisabled2, isPressed: isPressedProp, preventFocusOnPress, shouldCancelOnPointerExit, allowTextSelectionOnPress, ref: domRef, ...domProps } = $f6c31cce2adf654f$var$usePressResponderContext(props);
  let [isPressed, setPressed] = (0, import_react24.useState)(false);
  let ref = (0, import_react24.useRef)({
    isPressed: false,
    ignoreEmulatedMouseEvents: false,
    didFirePressStart: false,
    isTriggeringEvent: false,
    activePointerId: null,
    target: null,
    isOverTarget: false,
    pointerType: null,
    disposables: []
  });
  let { addGlobalListener, removeAllGlobalListeners } = (0, $03deb23ff14920c4$export$4eaf04e54aa8eed6)();
  let triggerPressStart = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((originalEvent, pointerType) => {
    let state = ref.current;
    if (isDisabled2 || state.didFirePressStart) return false;
    let shouldStopPropagation = true;
    state.isTriggeringEvent = true;
    if (onPressStart) {
      let event = new $f6c31cce2adf654f$var$PressEvent("pressstart", pointerType, originalEvent);
      onPressStart(event);
      shouldStopPropagation = event.shouldStopPropagation;
    }
    if (onPressChange) onPressChange(true);
    state.isTriggeringEvent = false;
    state.didFirePressStart = true;
    setPressed(true);
    return shouldStopPropagation;
  });
  let triggerPressEnd = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((originalEvent, pointerType, wasPressed = true) => {
    let state = ref.current;
    if (!state.didFirePressStart) return false;
    state.didFirePressStart = false;
    state.isTriggeringEvent = true;
    let shouldStopPropagation = true;
    if (onPressEnd) {
      let event = new $f6c31cce2adf654f$var$PressEvent("pressend", pointerType, originalEvent);
      onPressEnd(event);
      shouldStopPropagation = event.shouldStopPropagation;
    }
    if (onPressChange) onPressChange(false);
    setPressed(false);
    if (onPress && wasPressed && !isDisabled2) {
      let event = new $f6c31cce2adf654f$var$PressEvent("press", pointerType, originalEvent);
      onPress(event);
      shouldStopPropagation && (shouldStopPropagation = event.shouldStopPropagation);
    }
    state.isTriggeringEvent = false;
    return shouldStopPropagation;
  });
  let triggerPressUp = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((originalEvent, pointerType) => {
    let state = ref.current;
    if (isDisabled2) return false;
    if (onPressUp) {
      state.isTriggeringEvent = true;
      let event = new $f6c31cce2adf654f$var$PressEvent("pressup", pointerType, originalEvent);
      onPressUp(event);
      state.isTriggeringEvent = false;
      return event.shouldStopPropagation;
    }
    return true;
  });
  let cancel = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((e8) => {
    let state = ref.current;
    if (state.isPressed && state.target) {
      if (state.didFirePressStart && state.pointerType != null) triggerPressEnd($f6c31cce2adf654f$var$createEvent(state.target, e8), state.pointerType, false);
      state.isPressed = false;
      state.isOverTarget = false;
      state.activePointerId = null;
      state.pointerType = null;
      removeAllGlobalListeners();
      if (!allowTextSelectionOnPress) (0, $14c0b72509d70225$export$b0d6fa1ab32e3295)(state.target);
      for (let dispose of state.disposables) dispose();
      state.disposables = [];
    }
  });
  let cancelOnPointerExit = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((e8) => {
    if (shouldCancelOnPointerExit) cancel(e8);
  });
  let triggerClick = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((e8) => {
    onClick === null || onClick === void 0 ? void 0 : onClick(e8);
  });
  let triggerSyntheticClick = (0, $8ae05eaa5c114e9c$export$7f54fc3180508a52)((e8, target) => {
    if (onClick) {
      let event = new MouseEvent("click", e8);
      (0, $8a9cb279dc87e130$export$c2b7abe5d61ec696)(event, target);
      onClick((0, $8a9cb279dc87e130$export$525bc4921d56d4a)(event));
    }
  });
  let pressProps = (0, import_react24.useMemo)(() => {
    let state = ref.current;
    let pressProps2 = {
      onKeyDown(e8) {
        if ($f6c31cce2adf654f$var$isValidKeyboardEvent(e8.nativeEvent, e8.currentTarget) && (0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) {
          var _state_metaKeyEvents;
          if ($f6c31cce2adf654f$var$shouldPreventDefaultKeyboard((0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent), e8.key)) e8.preventDefault();
          let shouldStopPropagation = true;
          if (!state.isPressed && !e8.repeat) {
            state.target = e8.currentTarget;
            state.isPressed = true;
            state.pointerType = "keyboard";
            shouldStopPropagation = triggerPressStart(e8, "keyboard");
            let originalTarget = e8.currentTarget;
            let pressUp = (e9) => {
              if ($f6c31cce2adf654f$var$isValidKeyboardEvent(e9, originalTarget) && !e9.repeat && (0, $d4ee10de306f2510$export$4282f70798064fe0)(originalTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e9)) && state.target) triggerPressUp($f6c31cce2adf654f$var$createEvent(state.target, e9), "keyboard");
            };
            addGlobalListener((0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.currentTarget), "keyup", (0, $ff5963eb1fccf552$export$e08e3b67e392101e)(pressUp, onKeyUp), true);
          }
          if (shouldStopPropagation) e8.stopPropagation();
          if (e8.metaKey && (0, $c87311424ea30a05$export$9ac100e40613ea10)()) (_state_metaKeyEvents = state.metaKeyEvents) === null || _state_metaKeyEvents === void 0 ? void 0 : _state_metaKeyEvents.set(e8.key, e8.nativeEvent);
        } else if (e8.key === "Meta") state.metaKeyEvents = /* @__PURE__ */ new Map();
      },
      onClick(e8) {
        if (e8 && !(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (e8 && e8.button === 0 && !state.isTriggeringEvent && !(0, $ea8dcbcb9ea1b556$export$95185d699e05d4d7).isOpening) {
          let shouldStopPropagation = true;
          if (isDisabled2) e8.preventDefault();
          if (!state.ignoreEmulatedMouseEvents && !state.isPressed && (state.pointerType === "virtual" || (0, $6a7db85432448f7f$export$60278871457622de)(e8.nativeEvent))) {
            let stopPressStart = triggerPressStart(e8, "virtual");
            let stopPressUp = triggerPressUp(e8, "virtual");
            let stopPressEnd = triggerPressEnd(e8, "virtual");
            triggerClick(e8);
            shouldStopPropagation = stopPressStart && stopPressUp && stopPressEnd;
          } else if (state.isPressed && state.pointerType !== "keyboard") {
            let pointerType = state.pointerType || e8.nativeEvent.pointerType || "virtual";
            shouldStopPropagation = triggerPressEnd($f6c31cce2adf654f$var$createEvent(e8.currentTarget, e8), pointerType, true);
            state.isOverTarget = false;
            triggerClick(e8);
            cancel(e8);
          }
          state.ignoreEmulatedMouseEvents = false;
          if (shouldStopPropagation) e8.stopPropagation();
        }
      }
    };
    let onKeyUp = (e8) => {
      var _state_metaKeyEvents;
      if (state.isPressed && state.target && $f6c31cce2adf654f$var$isValidKeyboardEvent(e8, state.target)) {
        var _state_metaKeyEvents1;
        if ($f6c31cce2adf654f$var$shouldPreventDefaultKeyboard((0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8), e8.key)) e8.preventDefault();
        let target = (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8);
        let wasPressed = (0, $d4ee10de306f2510$export$4282f70798064fe0)(state.target, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8));
        triggerPressEnd($f6c31cce2adf654f$var$createEvent(state.target, e8), "keyboard", wasPressed);
        if (wasPressed) triggerSyntheticClick(e8, state.target);
        removeAllGlobalListeners();
        if (e8.key !== "Enter" && $f6c31cce2adf654f$var$isHTMLAnchorLink(state.target) && (0, $d4ee10de306f2510$export$4282f70798064fe0)(state.target, target) && !e8[$f6c31cce2adf654f$var$LINK_CLICKED]) {
          e8[$f6c31cce2adf654f$var$LINK_CLICKED] = true;
          (0, $ea8dcbcb9ea1b556$export$95185d699e05d4d7)(state.target, e8, false);
        }
        state.isPressed = false;
        (_state_metaKeyEvents1 = state.metaKeyEvents) === null || _state_metaKeyEvents1 === void 0 ? void 0 : _state_metaKeyEvents1.delete(e8.key);
      } else if (e8.key === "Meta" && ((_state_metaKeyEvents = state.metaKeyEvents) === null || _state_metaKeyEvents === void 0 ? void 0 : _state_metaKeyEvents.size)) {
        var _state_target;
        let events = state.metaKeyEvents;
        state.metaKeyEvents = void 0;
        for (let event of events.values()) (_state_target = state.target) === null || _state_target === void 0 ? void 0 : _state_target.dispatchEvent(new KeyboardEvent("keyup", event));
      }
    };
    if (typeof PointerEvent !== "undefined") {
      pressProps2.onPointerDown = (e8) => {
        if (e8.button !== 0 || !(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if ((0, $6a7db85432448f7f$export$29bf1b5f2c56cf63)(e8.nativeEvent)) {
          state.pointerType = "virtual";
          return;
        }
        state.pointerType = e8.pointerType;
        let shouldStopPropagation = true;
        if (!state.isPressed) {
          state.isPressed = true;
          state.isOverTarget = true;
          state.activePointerId = e8.pointerId;
          state.target = e8.currentTarget;
          if (!allowTextSelectionOnPress) (0, $14c0b72509d70225$export$16a4697467175487)(state.target);
          shouldStopPropagation = triggerPressStart(e8, state.pointerType);
          let target = (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent);
          if ("releasePointerCapture" in target) target.releasePointerCapture(e8.pointerId);
          addGlobalListener((0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.currentTarget), "pointerup", onPointerUp, false);
          addGlobalListener((0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.currentTarget), "pointercancel", onPointerCancel, false);
        }
        if (shouldStopPropagation) e8.stopPropagation();
      };
      pressProps2.onMouseDown = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (e8.button === 0) {
          if (preventFocusOnPress) {
            let dispose = (0, $8a9cb279dc87e130$export$cabe61c495ee3649)(e8.target);
            if (dispose) state.disposables.push(dispose);
          }
          e8.stopPropagation();
        }
      };
      pressProps2.onPointerUp = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent)) || state.pointerType === "virtual") return;
        if (e8.button === 0) triggerPressUp(e8, state.pointerType || e8.pointerType);
      };
      pressProps2.onPointerEnter = (e8) => {
        if (e8.pointerId === state.activePointerId && state.target && !state.isOverTarget && state.pointerType != null) {
          state.isOverTarget = true;
          triggerPressStart($f6c31cce2adf654f$var$createEvent(state.target, e8), state.pointerType);
        }
      };
      pressProps2.onPointerLeave = (e8) => {
        if (e8.pointerId === state.activePointerId && state.target && state.isOverTarget && state.pointerType != null) {
          state.isOverTarget = false;
          triggerPressEnd($f6c31cce2adf654f$var$createEvent(state.target, e8), state.pointerType, false);
          cancelOnPointerExit(e8);
        }
      };
      let onPointerUp = (e8) => {
        if (e8.pointerId === state.activePointerId && state.isPressed && e8.button === 0 && state.target) {
          if ((0, $d4ee10de306f2510$export$4282f70798064fe0)(state.target, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8)) && state.pointerType != null) {
            let clicked = false;
            let timeout = setTimeout(() => {
              if (state.isPressed && state.target instanceof HTMLElement) {
                if (clicked) cancel(e8);
                else {
                  (0, $7215afc6de606d6b$export$de79e2c695e052f3)(state.target);
                  state.target.click();
                }
              }
            }, 80);
            addGlobalListener(e8.currentTarget, "click", () => clicked = true, true);
            state.disposables.push(() => clearTimeout(timeout));
          } else cancel(e8);
          state.isOverTarget = false;
        }
      };
      let onPointerCancel = (e8) => {
        cancel(e8);
      };
      pressProps2.onDragStart = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        cancel(e8);
      };
    } else if (false) {
      pressProps2.onMouseDown = (e8) => {
        if (e8.button !== 0 || !(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (state.ignoreEmulatedMouseEvents) {
          e8.stopPropagation();
          return;
        }
        state.isPressed = true;
        state.isOverTarget = true;
        state.target = e8.currentTarget;
        state.pointerType = (0, $6a7db85432448f7f$export$60278871457622de)(e8.nativeEvent) ? "virtual" : "mouse";
        let shouldStopPropagation = (0, import_react_dom2.flushSync)(() => triggerPressStart(e8, state.pointerType));
        if (shouldStopPropagation) e8.stopPropagation();
        if (preventFocusOnPress) {
          let dispose = (0, $8a9cb279dc87e130$export$cabe61c495ee3649)(e8.target);
          if (dispose) state.disposables.push(dispose);
        }
        addGlobalListener((0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.currentTarget), "mouseup", onMouseUp, false);
      };
      pressProps2.onMouseEnter = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        let shouldStopPropagation = true;
        if (state.isPressed && !state.ignoreEmulatedMouseEvents && state.pointerType != null) {
          state.isOverTarget = true;
          shouldStopPropagation = triggerPressStart(e8, state.pointerType);
        }
        if (shouldStopPropagation) e8.stopPropagation();
      };
      pressProps2.onMouseLeave = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        let shouldStopPropagation = true;
        if (state.isPressed && !state.ignoreEmulatedMouseEvents && state.pointerType != null) {
          state.isOverTarget = false;
          shouldStopPropagation = triggerPressEnd(e8, state.pointerType, false);
          cancelOnPointerExit(e8);
        }
        if (shouldStopPropagation) e8.stopPropagation();
      };
      pressProps2.onMouseUp = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (!state.ignoreEmulatedMouseEvents && e8.button === 0) triggerPressUp(e8, state.pointerType || "mouse");
      };
      let onMouseUp = (e8) => {
        if (e8.button !== 0) return;
        if (state.ignoreEmulatedMouseEvents) {
          state.ignoreEmulatedMouseEvents = false;
          return;
        }
        if (state.target && state.target.contains(e8.target) && state.pointerType != null) ;
        else cancel(e8);
        state.isOverTarget = false;
      };
      pressProps2.onTouchStart = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        let touch = $f6c31cce2adf654f$var$getTouchFromEvent(e8.nativeEvent);
        if (!touch) return;
        state.activePointerId = touch.identifier;
        state.ignoreEmulatedMouseEvents = true;
        state.isOverTarget = true;
        state.isPressed = true;
        state.target = e8.currentTarget;
        state.pointerType = "touch";
        if (!allowTextSelectionOnPress) (0, $14c0b72509d70225$export$16a4697467175487)(state.target);
        let shouldStopPropagation = triggerPressStart($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType);
        if (shouldStopPropagation) e8.stopPropagation();
        addGlobalListener((0, $431fbd86ca7dc216$export$f21a1ffae260145a)(e8.currentTarget), "scroll", onScroll, true);
      };
      pressProps2.onTouchMove = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (!state.isPressed) {
          e8.stopPropagation();
          return;
        }
        let touch = $f6c31cce2adf654f$var$getTouchById(e8.nativeEvent, state.activePointerId);
        let shouldStopPropagation = true;
        if (touch && $f6c31cce2adf654f$var$isOverTarget(touch, e8.currentTarget)) {
          if (!state.isOverTarget && state.pointerType != null) {
            state.isOverTarget = true;
            shouldStopPropagation = triggerPressStart($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType);
          }
        } else if (state.isOverTarget && state.pointerType != null) {
          state.isOverTarget = false;
          shouldStopPropagation = triggerPressEnd($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType, false);
          cancelOnPointerExit($f6c31cce2adf654f$var$createTouchEvent(state.target, e8));
        }
        if (shouldStopPropagation) e8.stopPropagation();
      };
      pressProps2.onTouchEnd = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        if (!state.isPressed) {
          e8.stopPropagation();
          return;
        }
        let touch = $f6c31cce2adf654f$var$getTouchById(e8.nativeEvent, state.activePointerId);
        let shouldStopPropagation = true;
        if (touch && $f6c31cce2adf654f$var$isOverTarget(touch, e8.currentTarget) && state.pointerType != null) {
          triggerPressUp($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType);
          shouldStopPropagation = triggerPressEnd($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType);
          triggerSyntheticClick(e8.nativeEvent, state.target);
        } else if (state.isOverTarget && state.pointerType != null) shouldStopPropagation = triggerPressEnd($f6c31cce2adf654f$var$createTouchEvent(state.target, e8), state.pointerType, false);
        if (shouldStopPropagation) e8.stopPropagation();
        state.isPressed = false;
        state.activePointerId = null;
        state.isOverTarget = false;
        state.ignoreEmulatedMouseEvents = true;
        if (state.target && !allowTextSelectionOnPress) (0, $14c0b72509d70225$export$b0d6fa1ab32e3295)(state.target);
        removeAllGlobalListeners();
      };
      pressProps2.onTouchCancel = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        e8.stopPropagation();
        if (state.isPressed) cancel($f6c31cce2adf654f$var$createTouchEvent(state.target, e8));
      };
      let onScroll = (e8) => {
        if (state.isPressed && (0, $d4ee10de306f2510$export$4282f70798064fe0)((0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8), state.target)) cancel({
          currentTarget: state.target,
          shiftKey: false,
          ctrlKey: false,
          metaKey: false,
          altKey: false
        });
      };
      pressProps2.onDragStart = (e8) => {
        if (!(0, $d4ee10de306f2510$export$4282f70798064fe0)(e8.currentTarget, (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent))) return;
        cancel(e8);
      };
    }
    return pressProps2;
  }, [
    addGlobalListener,
    isDisabled2,
    preventFocusOnPress,
    removeAllGlobalListeners,
    allowTextSelectionOnPress,
    cancel,
    cancelOnPointerExit,
    triggerPressEnd,
    triggerPressStart,
    triggerPressUp,
    triggerClick,
    triggerSyntheticClick
  ]);
  (0, import_react24.useEffect)(() => {
    let element = domRef === null || domRef === void 0 ? void 0 : domRef.current;
    if (element && element instanceof (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element).Element) {
      let style = (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element).getComputedStyle(element);
      if (style.touchAction === "auto")
        element.style.touchAction = "pan-x pan-y pinch-zoom";
    }
  }, [
    domRef
  ]);
  (0, import_react24.useEffect)(() => {
    let state = ref.current;
    return () => {
      var _state_target;
      if (!allowTextSelectionOnPress) (0, $14c0b72509d70225$export$b0d6fa1ab32e3295)((_state_target = state.target) !== null && _state_target !== void 0 ? _state_target : void 0);
      for (let dispose of state.disposables) dispose();
      state.disposables = [];
    };
  }, [
    allowTextSelectionOnPress
  ]);
  return {
    isPressed: isPressedProp || isPressed,
    pressProps: (0, $3ef42575df84b30b$export$9d1611c77c2fe928)(domProps, pressProps)
  };
}
function $f6c31cce2adf654f$var$isHTMLAnchorLink(target) {
  return target.tagName === "A" && target.hasAttribute("href");
}
function $f6c31cce2adf654f$var$isValidKeyboardEvent(event, currentTarget) {
  const { key, code } = event;
  const element = currentTarget;
  const role = element.getAttribute("role");
  return (key === "Enter" || key === " " || key === "Spacebar" || code === "Space") && !(element instanceof (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element).HTMLInputElement && !$f6c31cce2adf654f$var$isValidInputKey(element, key) || element instanceof (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element).HTMLTextAreaElement || element.isContentEditable) && // Links should only trigger with Enter key
  !((role === "link" || !role && $f6c31cce2adf654f$var$isHTMLAnchorLink(element)) && key !== "Enter");
}
function $f6c31cce2adf654f$var$createEvent(target, e8) {
  let clientX = e8.clientX;
  let clientY = e8.clientY;
  return {
    currentTarget: target,
    shiftKey: e8.shiftKey,
    ctrlKey: e8.ctrlKey,
    metaKey: e8.metaKey,
    altKey: e8.altKey,
    clientX,
    clientY
  };
}
function $f6c31cce2adf654f$var$shouldPreventDefaultUp(target) {
  if (target instanceof HTMLInputElement) return false;
  if (target instanceof HTMLButtonElement) return target.type !== "submit" && target.type !== "reset";
  if ($f6c31cce2adf654f$var$isHTMLAnchorLink(target)) return false;
  return true;
}
function $f6c31cce2adf654f$var$shouldPreventDefaultKeyboard(target, key) {
  if (target instanceof HTMLInputElement) return !$f6c31cce2adf654f$var$isValidInputKey(target, key);
  return $f6c31cce2adf654f$var$shouldPreventDefaultUp(target);
}
var $f6c31cce2adf654f$var$nonTextInputTypes = /* @__PURE__ */ new Set([
  "checkbox",
  "radio",
  "range",
  "color",
  "file",
  "image",
  "button",
  "submit",
  "reset"
]);
function $f6c31cce2adf654f$var$isValidInputKey(target, key) {
  return target.type === "checkbox" || target.type === "radio" ? key === " " : $f6c31cce2adf654f$var$nonTextInputTypes.has(target.type);
}

// node_modules/@react-aria/interactions/dist/useFocusVisible.mjs
var import_react25 = __toESM(require_react(), 1);
var $507fabe10e71c6fb$var$currentModality = null;
var $507fabe10e71c6fb$var$changeHandlers = /* @__PURE__ */ new Set();
var $507fabe10e71c6fb$export$d90243b58daecda7 = /* @__PURE__ */ new Map();
var $507fabe10e71c6fb$var$hasEventBeforeFocus = false;
var $507fabe10e71c6fb$var$hasBlurredWindowRecently = false;
var $507fabe10e71c6fb$var$FOCUS_VISIBLE_INPUT_KEYS = {
  Tab: true,
  Escape: true
};
function $507fabe10e71c6fb$var$triggerChangeHandlers(modality, e8) {
  for (let handler of $507fabe10e71c6fb$var$changeHandlers) handler(modality, e8);
}
function $507fabe10e71c6fb$var$isValidKey(e8) {
  return !(e8.metaKey || !(0, $c87311424ea30a05$export$9ac100e40613ea10)() && e8.altKey || e8.ctrlKey || e8.key === "Control" || e8.key === "Shift" || e8.key === "Meta");
}
function $507fabe10e71c6fb$var$handleKeyboardEvent(e8) {
  $507fabe10e71c6fb$var$hasEventBeforeFocus = true;
  if ($507fabe10e71c6fb$var$isValidKey(e8)) {
    $507fabe10e71c6fb$var$currentModality = "keyboard";
    $507fabe10e71c6fb$var$triggerChangeHandlers("keyboard", e8);
  }
}
function $507fabe10e71c6fb$var$handlePointerEvent(e8) {
  $507fabe10e71c6fb$var$currentModality = "pointer";
  if (e8.type === "mousedown" || e8.type === "pointerdown") {
    $507fabe10e71c6fb$var$hasEventBeforeFocus = true;
    $507fabe10e71c6fb$var$triggerChangeHandlers("pointer", e8);
  }
}
function $507fabe10e71c6fb$var$handleClickEvent(e8) {
  if ((0, $6a7db85432448f7f$export$60278871457622de)(e8)) {
    $507fabe10e71c6fb$var$hasEventBeforeFocus = true;
    $507fabe10e71c6fb$var$currentModality = "virtual";
  }
}
function $507fabe10e71c6fb$var$handleFocusEvent(e8) {
  if (e8.target === window || e8.target === document || (0, $8a9cb279dc87e130$export$fda7da73ab5d4c48) || !e8.isTrusted) return;
  if (!$507fabe10e71c6fb$var$hasEventBeforeFocus && !$507fabe10e71c6fb$var$hasBlurredWindowRecently) {
    $507fabe10e71c6fb$var$currentModality = "virtual";
    $507fabe10e71c6fb$var$triggerChangeHandlers("virtual", e8);
  }
  $507fabe10e71c6fb$var$hasEventBeforeFocus = false;
  $507fabe10e71c6fb$var$hasBlurredWindowRecently = false;
}
function $507fabe10e71c6fb$var$handleWindowBlur() {
  if (0, $8a9cb279dc87e130$export$fda7da73ab5d4c48) return;
  $507fabe10e71c6fb$var$hasEventBeforeFocus = false;
  $507fabe10e71c6fb$var$hasBlurredWindowRecently = true;
}
function $507fabe10e71c6fb$var$setupGlobalFocusEvents(element) {
  if (typeof window === "undefined" || $507fabe10e71c6fb$export$d90243b58daecda7.get((0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element))) return;
  const windowObject = (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element);
  const documentObject = (0, $431fbd86ca7dc216$export$b204af158042fbac)(element);
  let focus = windowObject.HTMLElement.prototype.focus;
  windowObject.HTMLElement.prototype.focus = function() {
    $507fabe10e71c6fb$var$hasEventBeforeFocus = true;
    focus.apply(this, arguments);
  };
  documentObject.addEventListener("keydown", $507fabe10e71c6fb$var$handleKeyboardEvent, true);
  documentObject.addEventListener("keyup", $507fabe10e71c6fb$var$handleKeyboardEvent, true);
  documentObject.addEventListener("click", $507fabe10e71c6fb$var$handleClickEvent, true);
  windowObject.addEventListener("focus", $507fabe10e71c6fb$var$handleFocusEvent, true);
  windowObject.addEventListener("blur", $507fabe10e71c6fb$var$handleWindowBlur, false);
  if (typeof PointerEvent !== "undefined") {
    documentObject.addEventListener("pointerdown", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.addEventListener("pointermove", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.addEventListener("pointerup", $507fabe10e71c6fb$var$handlePointerEvent, true);
  } else if (false) {
    documentObject.addEventListener("mousedown", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.addEventListener("mousemove", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.addEventListener("mouseup", $507fabe10e71c6fb$var$handlePointerEvent, true);
  }
  windowObject.addEventListener("beforeunload", () => {
    $507fabe10e71c6fb$var$tearDownWindowFocusTracking(element);
  }, {
    once: true
  });
  $507fabe10e71c6fb$export$d90243b58daecda7.set(windowObject, {
    focus
  });
}
var $507fabe10e71c6fb$var$tearDownWindowFocusTracking = (element, loadListener) => {
  const windowObject = (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(element);
  const documentObject = (0, $431fbd86ca7dc216$export$b204af158042fbac)(element);
  if (loadListener) documentObject.removeEventListener("DOMContentLoaded", loadListener);
  if (!$507fabe10e71c6fb$export$d90243b58daecda7.has(windowObject)) return;
  windowObject.HTMLElement.prototype.focus = $507fabe10e71c6fb$export$d90243b58daecda7.get(windowObject).focus;
  documentObject.removeEventListener("keydown", $507fabe10e71c6fb$var$handleKeyboardEvent, true);
  documentObject.removeEventListener("keyup", $507fabe10e71c6fb$var$handleKeyboardEvent, true);
  documentObject.removeEventListener("click", $507fabe10e71c6fb$var$handleClickEvent, true);
  windowObject.removeEventListener("focus", $507fabe10e71c6fb$var$handleFocusEvent, true);
  windowObject.removeEventListener("blur", $507fabe10e71c6fb$var$handleWindowBlur, false);
  if (typeof PointerEvent !== "undefined") {
    documentObject.removeEventListener("pointerdown", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.removeEventListener("pointermove", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.removeEventListener("pointerup", $507fabe10e71c6fb$var$handlePointerEvent, true);
  } else if (false) {
    documentObject.removeEventListener("mousedown", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.removeEventListener("mousemove", $507fabe10e71c6fb$var$handlePointerEvent, true);
    documentObject.removeEventListener("mouseup", $507fabe10e71c6fb$var$handlePointerEvent, true);
  }
  $507fabe10e71c6fb$export$d90243b58daecda7.delete(windowObject);
};
function $507fabe10e71c6fb$export$2f1888112f558a7d(element) {
  const documentObject = (0, $431fbd86ca7dc216$export$b204af158042fbac)(element);
  let loadListener;
  if (documentObject.readyState !== "loading") $507fabe10e71c6fb$var$setupGlobalFocusEvents(element);
  else {
    loadListener = () => {
      $507fabe10e71c6fb$var$setupGlobalFocusEvents(element);
    };
    documentObject.addEventListener("DOMContentLoaded", loadListener);
  }
  return () => $507fabe10e71c6fb$var$tearDownWindowFocusTracking(element, loadListener);
}
if (typeof document !== "undefined") $507fabe10e71c6fb$export$2f1888112f558a7d();
function $507fabe10e71c6fb$export$b9b3dfddab17db27() {
  return $507fabe10e71c6fb$var$currentModality !== "pointer";
}
function $507fabe10e71c6fb$export$630ff653c5ada6a9() {
  return $507fabe10e71c6fb$var$currentModality;
}
var $507fabe10e71c6fb$var$nonTextInputTypes = /* @__PURE__ */ new Set([
  "checkbox",
  "radio",
  "range",
  "color",
  "file",
  "image",
  "button",
  "submit",
  "reset"
]);
function $507fabe10e71c6fb$var$isKeyboardFocusEvent(isTextInput, modality, e8) {
  let document1 = (0, $431fbd86ca7dc216$export$b204af158042fbac)(e8 === null || e8 === void 0 ? void 0 : e8.target);
  const IHTMLInputElement = typeof window !== "undefined" ? (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(e8 === null || e8 === void 0 ? void 0 : e8.target).HTMLInputElement : HTMLInputElement;
  const IHTMLTextAreaElement = typeof window !== "undefined" ? (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(e8 === null || e8 === void 0 ? void 0 : e8.target).HTMLTextAreaElement : HTMLTextAreaElement;
  const IHTMLElement = typeof window !== "undefined" ? (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(e8 === null || e8 === void 0 ? void 0 : e8.target).HTMLElement : HTMLElement;
  const IKeyboardEvent = typeof window !== "undefined" ? (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(e8 === null || e8 === void 0 ? void 0 : e8.target).KeyboardEvent : KeyboardEvent;
  isTextInput = isTextInput || document1.activeElement instanceof IHTMLInputElement && !$507fabe10e71c6fb$var$nonTextInputTypes.has(document1.activeElement.type) || document1.activeElement instanceof IHTMLTextAreaElement || document1.activeElement instanceof IHTMLElement && document1.activeElement.isContentEditable;
  return !(isTextInput && modality === "keyboard" && e8 instanceof IKeyboardEvent && !$507fabe10e71c6fb$var$FOCUS_VISIBLE_INPUT_KEYS[e8.key]);
}
function $507fabe10e71c6fb$export$ec71b4b83ac08ec3(fn, deps, opts) {
  $507fabe10e71c6fb$var$setupGlobalFocusEvents();
  (0, import_react25.useEffect)(() => {
    let handler = (modality, e8) => {
      if (!$507fabe10e71c6fb$var$isKeyboardFocusEvent(!!(opts === null || opts === void 0 ? void 0 : opts.isTextInput), modality, e8)) return;
      fn($507fabe10e71c6fb$export$b9b3dfddab17db27());
    };
    $507fabe10e71c6fb$var$changeHandlers.add(handler);
    return () => {
      $507fabe10e71c6fb$var$changeHandlers.delete(handler);
    };
  }, deps);
}

// node_modules/@react-aria/interactions/dist/focusSafely.mjs
function $3ad3f6e1647bc98d$export$80f3e147d781571c(element) {
  const ownerDocument = (0, $431fbd86ca7dc216$export$b204af158042fbac)(element);
  const activeElement2 = (0, $d4ee10de306f2510$export$cd4e5573fbe2b576)(ownerDocument);
  if ((0, $507fabe10e71c6fb$export$630ff653c5ada6a9)() === "virtual") {
    let lastFocusedElement = activeElement2;
    (0, $bbed8b41f857bcc0$export$24490316f764c430)(() => {
      if ((0, $d4ee10de306f2510$export$cd4e5573fbe2b576)(ownerDocument) === lastFocusedElement && element.isConnected) (0, $7215afc6de606d6b$export$de79e2c695e052f3)(element);
    });
  } else (0, $7215afc6de606d6b$export$de79e2c695e052f3)(element);
}

// node_modules/@react-aria/interactions/dist/useFocus.mjs
var import_react26 = __toESM(require_react(), 1);
function $a1ea59d68270f0dd$export$f8168d8dd8fd66e6(props) {
  let { isDisabled: isDisabled2, onFocus: onFocusProp, onBlur: onBlurProp, onFocusChange } = props;
  const onBlur = (0, import_react26.useCallback)((e8) => {
    if (e8.target === e8.currentTarget) {
      if (onBlurProp) onBlurProp(e8);
      if (onFocusChange) onFocusChange(false);
      return true;
    }
  }, [
    onBlurProp,
    onFocusChange
  ]);
  const onSyntheticFocus = (0, $8a9cb279dc87e130$export$715c682d09d639cc)(onBlur);
  const onFocus = (0, import_react26.useCallback)((e8) => {
    const ownerDocument = (0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.target);
    const activeElement2 = ownerDocument ? (0, $d4ee10de306f2510$export$cd4e5573fbe2b576)(ownerDocument) : (0, $d4ee10de306f2510$export$cd4e5573fbe2b576)();
    if (e8.target === e8.currentTarget && activeElement2 === (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent)) {
      if (onFocusProp) onFocusProp(e8);
      if (onFocusChange) onFocusChange(true);
      onSyntheticFocus(e8);
    }
  }, [
    onFocusChange,
    onFocusProp,
    onSyntheticFocus
  ]);
  return {
    focusProps: {
      onFocus: !isDisabled2 && (onFocusProp || onFocusChange || onBlurProp) ? onFocus : void 0,
      onBlur: !isDisabled2 && (onBlurProp || onFocusChange) ? onBlur : void 0
    }
  };
}

// node_modules/@react-aria/interactions/dist/createEventHandler.mjs
function $93925083ecbb358c$export$48d1ea6320830260(handler) {
  if (!handler) return void 0;
  let shouldStopPropagation = true;
  return (e8) => {
    let event = {
      ...e8,
      preventDefault() {
        e8.preventDefault();
      },
      isDefaultPrevented() {
        return e8.isDefaultPrevented();
      },
      stopPropagation() {
        if (shouldStopPropagation && true) console.error("stopPropagation is now the default behavior for events in React Spectrum. You can use continuePropagation() to revert this behavior.");
        else shouldStopPropagation = true;
      },
      continuePropagation() {
        shouldStopPropagation = false;
      },
      isPropagationStopped() {
        return shouldStopPropagation;
      }
    };
    handler(event);
    if (shouldStopPropagation) e8.stopPropagation();
  };
}

// node_modules/@react-aria/interactions/dist/useKeyboard.mjs
function $46d819fcbaf35654$export$8f71654801c2f7cd(props) {
  return {
    keyboardProps: props.isDisabled ? {} : {
      onKeyDown: (0, $93925083ecbb358c$export$48d1ea6320830260)(props.onKeyDown),
      onKeyUp: (0, $93925083ecbb358c$export$48d1ea6320830260)(props.onKeyUp)
    }
  };
}

// node_modules/@react-aria/interactions/dist/useFocusable.mjs
var import_react27 = __toESM(require_react(), 1);
var $f645667febf57a63$export$f9762fab77588ecb = (0, import_react27.default).createContext(null);
function $f645667febf57a63$var$useFocusableContext(ref) {
  let context = (0, import_react27.useContext)($f645667febf57a63$export$f9762fab77588ecb) || {};
  (0, $e7801be82b4b2a53$export$4debdb1a3f0fa79e)(context, ref);
  let { ref: _8, ...otherProps } = context;
  return otherProps;
}
var $f645667febf57a63$export$13f3202a3e5ddd5 = (0, import_react27.default).forwardRef(function FocusableProvider(props, ref) {
  let { children, ...otherProps } = props;
  let objRef = (0, $df56164dff5785e2$export$4338b53315abf666)(ref);
  let context = {
    ...otherProps,
    ref: objRef
  };
  return (0, import_react27.default).createElement($f645667febf57a63$export$f9762fab77588ecb.Provider, {
    value: context
  }, children);
});
function $f645667febf57a63$export$4c014de7c8940b4c(props, domRef) {
  let { focusProps } = (0, $a1ea59d68270f0dd$export$f8168d8dd8fd66e6)(props);
  let { keyboardProps } = (0, $46d819fcbaf35654$export$8f71654801c2f7cd)(props);
  let interactions = (0, $3ef42575df84b30b$export$9d1611c77c2fe928)(focusProps, keyboardProps);
  let domProps = $f645667febf57a63$var$useFocusableContext(domRef);
  let interactionProps = props.isDisabled ? {} : domProps;
  let autoFocusRef = (0, import_react27.useRef)(props.autoFocus);
  (0, import_react27.useEffect)(() => {
    if (autoFocusRef.current && domRef.current) (0, $3ad3f6e1647bc98d$export$80f3e147d781571c)(domRef.current);
    autoFocusRef.current = false;
  }, [
    domRef
  ]);
  let tabIndex = props.excludeFromTabOrder ? -1 : 0;
  if (props.isDisabled) tabIndex = void 0;
  return {
    focusableProps: (0, $3ef42575df84b30b$export$9d1611c77c2fe928)({
      ...interactions,
      tabIndex
    }, interactionProps)
  };
}
var $f645667febf57a63$export$35a3bebf7ef2d934 = (0, import_react27.forwardRef)(({ children, ...props }, ref) => {
  ref = (0, $df56164dff5785e2$export$4338b53315abf666)(ref);
  let { focusableProps } = $f645667febf57a63$export$4c014de7c8940b4c(props, ref);
  let child = (0, import_react27.default).Children.only(children);
  (0, import_react27.useEffect)(() => {
    if (false) return;
    let el = ref.current;
    if (!el || !(el instanceof (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(el).Element)) {
      console.error("<Focusable> child must forward its ref to a DOM element.");
      return;
    }
    if (!props.isDisabled && !(0, $b4b717babfbb907b$export$4c063cf1350e6fed)(el)) {
      console.warn("<Focusable> child must be focusable. Please ensure the tabIndex prop is passed through.");
      return;
    }
    if (el.localName !== "button" && el.localName !== "input" && el.localName !== "select" && el.localName !== "textarea" && el.localName !== "a" && el.localName !== "area" && el.localName !== "summary" && el.localName !== "img" && el.localName !== "svg") {
      let role = el.getAttribute("role");
      if (!role) console.warn("<Focusable> child must have an interactive ARIA role.");
      else if (
        // https://w3c.github.io/aria/#widget_roles
        role !== "application" && role !== "button" && role !== "checkbox" && role !== "combobox" && role !== "gridcell" && role !== "link" && role !== "menuitem" && role !== "menuitemcheckbox" && role !== "menuitemradio" && role !== "option" && role !== "radio" && role !== "searchbox" && role !== "separator" && role !== "slider" && role !== "spinbutton" && role !== "switch" && role !== "tab" && role !== "tabpanel" && role !== "textbox" && role !== "treeitem" && // aria-describedby is also announced on these roles
        role !== "img" && role !== "meter" && role !== "progressbar"
      ) console.warn(`<Focusable> child must have an interactive ARIA role. Got "${role}".`);
    }
  }, [
    ref,
    props.isDisabled
  ]);
  let childRef = parseInt((0, import_react27.default).version, 10) < 19 ? child.ref : child.props.ref;
  return (0, import_react27.default).cloneElement(child, {
    ...(0, $3ef42575df84b30b$export$9d1611c77c2fe928)(focusableProps, child.props),
    // @ts-ignore
    ref: (0, $5dc95899b306f630$export$c9058316764c140e)(childRef, ref)
  });
});

// node_modules/@react-aria/interactions/dist/Pressable.mjs
var import_react28 = __toESM(require_react(), 1);
var $3b117e43dc0ca95d$export$27c701ed9e449e99 = (0, import_react28.default).forwardRef(({ children, ...props }, ref) => {
  ref = (0, $df56164dff5785e2$export$4338b53315abf666)(ref);
  let { pressProps } = (0, $f6c31cce2adf654f$export$45712eceda6fad21)({
    ...props,
    ref
  });
  let { focusableProps } = (0, $f645667febf57a63$export$4c014de7c8940b4c)(props, ref);
  let child = (0, import_react28.default).Children.only(children);
  (0, import_react28.useEffect)(() => {
    if (false) return;
    let el = ref.current;
    if (!el || !(el instanceof (0, $431fbd86ca7dc216$export$f21a1ffae260145a)(el).Element)) {
      console.error("<Pressable> child must forward its ref to a DOM element.");
      return;
    }
    if (!(0, $b4b717babfbb907b$export$4c063cf1350e6fed)(el)) {
      console.warn("<Pressable> child must be focusable. Please ensure the tabIndex prop is passed through.");
      return;
    }
    if (el.localName !== "button" && el.localName !== "input" && el.localName !== "select" && el.localName !== "textarea" && el.localName !== "a" && el.localName !== "area" && el.localName !== "summary") {
      let role = el.getAttribute("role");
      if (!role) console.warn("<Pressable> child must have an interactive ARIA role.");
      else if (
        // https://w3c.github.io/aria/#widget_roles
        role !== "application" && role !== "button" && role !== "checkbox" && role !== "combobox" && role !== "gridcell" && role !== "link" && role !== "menuitem" && role !== "menuitemcheckbox" && role !== "menuitemradio" && role !== "option" && role !== "radio" && role !== "searchbox" && role !== "separator" && role !== "slider" && role !== "spinbutton" && role !== "switch" && role !== "tab" && role !== "textbox" && role !== "treeitem"
      ) console.warn(`<Pressable> child must have an interactive ARIA role. Got "${role}".`);
    }
  }, [
    ref
  ]);
  let childRef = parseInt((0, import_react28.default).version, 10) < 19 ? child.ref : child.props.ref;
  return (0, import_react28.default).cloneElement(child, {
    ...(0, $3ef42575df84b30b$export$9d1611c77c2fe928)(pressProps, focusableProps, child.props),
    // @ts-ignore
    ref: (0, $5dc95899b306f630$export$c9058316764c140e)(childRef, ref)
  });
});

// node_modules/@react-aria/interactions/dist/PressResponder.mjs
var import_react29 = __toESM(require_react(), 1);
var $f1ab8c75478c6f73$export$3351871ee4b288b8 = (0, import_react29.default).forwardRef(({ children, ...props }, ref) => {
  let isRegistered = (0, import_react29.useRef)(false);
  let prevContext = (0, import_react29.useContext)((0, $ae1eeba8b9eafd08$export$5165eccb35aaadb5));
  ref = (0, $df56164dff5785e2$export$4338b53315abf666)(ref || (prevContext === null || prevContext === void 0 ? void 0 : prevContext.ref));
  let context = (0, $3ef42575df84b30b$export$9d1611c77c2fe928)(prevContext || {}, {
    ...props,
    ref,
    register() {
      isRegistered.current = true;
      if (prevContext) prevContext.register();
    }
  });
  (0, $e7801be82b4b2a53$export$4debdb1a3f0fa79e)(prevContext, ref);
  (0, import_react29.useEffect)(() => {
    if (!isRegistered.current) {
      if (true) console.warn("A PressResponder was rendered without a pressable child. Either call the usePress hook, or wrap your DOM node with <Pressable> component.");
      isRegistered.current = true;
    }
  }, []);
  return (0, import_react29.default).createElement((0, $ae1eeba8b9eafd08$export$5165eccb35aaadb5).Provider, {
    value: context
  }, children);
});

// node_modules/@react-aria/interactions/dist/useFocusWithin.mjs
var import_react30 = __toESM(require_react(), 1);
function $9ab94262bd0047c7$export$420e68273165f4ec(props) {
  let { isDisabled: isDisabled2, onBlurWithin, onFocusWithin, onFocusWithinChange } = props;
  let state = (0, import_react30.useRef)({
    isFocusWithin: false
  });
  let { addGlobalListener, removeAllGlobalListeners } = (0, $03deb23ff14920c4$export$4eaf04e54aa8eed6)();
  let onBlur = (0, import_react30.useCallback)((e8) => {
    if (!e8.currentTarget.contains(e8.target)) return;
    if (state.current.isFocusWithin && !e8.currentTarget.contains(e8.relatedTarget)) {
      state.current.isFocusWithin = false;
      removeAllGlobalListeners();
      if (onBlurWithin) onBlurWithin(e8);
      if (onFocusWithinChange) onFocusWithinChange(false);
    }
  }, [
    onBlurWithin,
    onFocusWithinChange,
    state,
    removeAllGlobalListeners
  ]);
  let onSyntheticFocus = (0, $8a9cb279dc87e130$export$715c682d09d639cc)(onBlur);
  let onFocus = (0, import_react30.useCallback)((e8) => {
    if (!e8.currentTarget.contains(e8.target)) return;
    const ownerDocument = (0, $431fbd86ca7dc216$export$b204af158042fbac)(e8.target);
    const activeElement2 = (0, $d4ee10de306f2510$export$cd4e5573fbe2b576)(ownerDocument);
    if (!state.current.isFocusWithin && activeElement2 === (0, $d4ee10de306f2510$export$e58f029f0fbfdb29)(e8.nativeEvent)) {
      if (onFocusWithin) onFocusWithin(e8);
      if (onFocusWithinChange) onFocusWithinChange(true);
      state.current.isFocusWithin = true;
      onSyntheticFocus(e8);
      let currentTarget = e8.currentTarget;
      addGlobalListener(ownerDocument, "focus", (e9) => {
        if (state.current.isFocusWithin && !(0, $d4ee10de306f2510$export$4282f70798064fe0)(currentTarget, e9.target)) {
          let nativeEvent = new ownerDocument.defaultView.FocusEvent("blur", {
            relatedTarget: e9.target
          });
          (0, $8a9cb279dc87e130$export$c2b7abe5d61ec696)(nativeEvent, currentTarget);
          let event = (0, $8a9cb279dc87e130$export$525bc4921d56d4a)(nativeEvent);
          onBlur(event);
        }
      }, {
        capture: true
      });
    }
  }, [
    onFocusWithin,
    onFocusWithinChange,
    onSyntheticFocus,
    addGlobalListener,
    onBlur
  ]);
  if (isDisabled2) return {
    focusWithinProps: {
      // These cannot be null, that would conflict in mergeProps
      onFocus: void 0,
      onBlur: void 0
    }
  };
  return {
    focusWithinProps: {
      onFocus,
      onBlur
    }
  };
}

// node_modules/@react-aria/interactions/dist/useHover.mjs
var import_react31 = __toESM(require_react(), 1);
var $6179b936705e76d3$var$globalIgnoreEmulatedMouseEvents = false;
var $6179b936705e76d3$var$hoverCount = 0;
function $6179b936705e76d3$var$setGlobalIgnoreEmulatedMouseEvents() {
  $6179b936705e76d3$var$globalIgnoreEmulatedMouseEvents = true;
  setTimeout(() => {
    $6179b936705e76d3$var$globalIgnoreEmulatedMouseEvents = false;
  }, 50);
}
function $6179b936705e76d3$var$handleGlobalPointerEvent(e8) {
  if (e8.pointerType === "touch") $6179b936705e76d3$var$setGlobalIgnoreEmulatedMouseEvents();
}
function $6179b936705e76d3$var$setupGlobalTouchEvents() {
  if (typeof document === "undefined") return;
  if (typeof PointerEvent !== "undefined") document.addEventListener("pointerup", $6179b936705e76d3$var$handleGlobalPointerEvent);
  else if (false) document.addEventListener("touchend", $6179b936705e76d3$var$setGlobalIgnoreEmulatedMouseEvents);
  $6179b936705e76d3$var$hoverCount++;
  return () => {
    $6179b936705e76d3$var$hoverCount--;
    if ($6179b936705e76d3$var$hoverCount > 0) return;
    if (typeof PointerEvent !== "undefined") document.removeEventListener("pointerup", $6179b936705e76d3$var$handleGlobalPointerEvent);
    else if (false) document.removeEventListener("touchend", $6179b936705e76d3$var$setGlobalIgnoreEmulatedMouseEvents);
  };
}
function $6179b936705e76d3$export$ae780daf29e6d456(props) {
  let { onHoverStart, onHoverChange, onHoverEnd, isDisabled: isDisabled2 } = props;
  let [isHovered, setHovered] = (0, import_react31.useState)(false);
  let state = (0, import_react31.useRef)({
    isHovered: false,
    ignoreEmulatedMouseEvents: false,
    pointerType: "",
    target: null
  }).current;
  (0, import_react31.useEffect)($6179b936705e76d3$var$setupGlobalTouchEvents, []);
  let { addGlobalListener, removeAllGlobalListeners } = (0, $03deb23ff14920c4$export$4eaf04e54aa8eed6)();
  let { hoverProps, triggerHoverEnd } = (0, import_react31.useMemo)(() => {
    let triggerHoverStart = (event, pointerType) => {
      state.pointerType = pointerType;
      if (isDisabled2 || pointerType === "touch" || state.isHovered || !event.currentTarget.contains(event.target)) return;
      state.isHovered = true;
      let target = event.currentTarget;
      state.target = target;
      addGlobalListener((0, $431fbd86ca7dc216$export$b204af158042fbac)(event.target), "pointerover", (e8) => {
        if (state.isHovered && state.target && !(0, $d4ee10de306f2510$export$4282f70798064fe0)(state.target, e8.target)) triggerHoverEnd2(e8, e8.pointerType);
      }, {
        capture: true
      });
      if (onHoverStart) onHoverStart({
        type: "hoverstart",
        target,
        pointerType
      });
      if (onHoverChange) onHoverChange(true);
      setHovered(true);
    };
    let triggerHoverEnd2 = (event, pointerType) => {
      let target = state.target;
      state.pointerType = "";
      state.target = null;
      if (pointerType === "touch" || !state.isHovered || !target) return;
      state.isHovered = false;
      removeAllGlobalListeners();
      if (onHoverEnd) onHoverEnd({
        type: "hoverend",
        target,
        pointerType
      });
      if (onHoverChange) onHoverChange(false);
      setHovered(false);
    };
    let hoverProps2 = {};
    if (typeof PointerEvent !== "undefined") {
      hoverProps2.onPointerEnter = (e8) => {
        if ($6179b936705e76d3$var$globalIgnoreEmulatedMouseEvents && e8.pointerType === "mouse") return;
        triggerHoverStart(e8, e8.pointerType);
      };
      hoverProps2.onPointerLeave = (e8) => {
        if (!isDisabled2 && e8.currentTarget.contains(e8.target)) triggerHoverEnd2(e8, e8.pointerType);
      };
    } else if (false) {
      hoverProps2.onTouchStart = () => {
        state.ignoreEmulatedMouseEvents = true;
      };
      hoverProps2.onMouseEnter = (e8) => {
        if (!state.ignoreEmulatedMouseEvents && !$6179b936705e76d3$var$globalIgnoreEmulatedMouseEvents) triggerHoverStart(e8, "mouse");
        state.ignoreEmulatedMouseEvents = false;
      };
      hoverProps2.onMouseLeave = (e8) => {
        if (!isDisabled2 && e8.currentTarget.contains(e8.target)) triggerHoverEnd2(e8, "mouse");
      };
    }
    return {
      hoverProps: hoverProps2,
      triggerHoverEnd: triggerHoverEnd2
    };
  }, [
    onHoverStart,
    onHoverChange,
    onHoverEnd,
    isDisabled2,
    state,
    addGlobalListener,
    removeAllGlobalListeners
  ]);
  (0, import_react31.useEffect)(() => {
    if (isDisabled2) triggerHoverEnd({
      currentTarget: state.target
    }, state.pointerType);
  }, [
    isDisabled2
  ]);
  return {
    hoverProps,
    isHovered
  };
}

// node_modules/@react-aria/interactions/dist/useInteractOutside.mjs
var import_react32 = __toESM(require_react(), 1);

// node_modules/@react-aria/interactions/dist/useMove.mjs
var import_react33 = __toESM(require_react(), 1);

// node_modules/@react-aria/interactions/dist/useScrollWheel.mjs
var import_react34 = __toESM(require_react(), 1);

// node_modules/@react-aria/interactions/dist/useLongPress.mjs
var import_react35 = __toESM(require_react(), 1);

// node_modules/@react-aria/focus/dist/FocusScope.mjs
var import_react36 = __toESM(require_react(), 1);
var $9bf71ea28793e738$var$FocusContext = (0, import_react36.default).createContext(null);
function $9bf71ea28793e738$var$isElementInScope(element, scope) {
  if (!element) return false;
  if (!scope) return false;
  return scope.some((node) => node.contains(element));
}
var $9bf71ea28793e738$var$Tree = class _$9bf71ea28793e738$var$Tree {
  get size() {
    return this.fastMap.size;
  }
  getTreeNode(data) {
    return this.fastMap.get(data);
  }
  addTreeNode(scopeRef, parent, nodeToRestore) {
    let parentNode = this.fastMap.get(parent !== null && parent !== void 0 ? parent : null);
    if (!parentNode) return;
    let node = new $9bf71ea28793e738$var$TreeNode({
      scopeRef
    });
    parentNode.addChild(node);
    node.parent = parentNode;
    this.fastMap.set(scopeRef, node);
    if (nodeToRestore) node.nodeToRestore = nodeToRestore;
  }
  addNode(node) {
    this.fastMap.set(node.scopeRef, node);
  }
  removeTreeNode(scopeRef) {
    if (scopeRef === null) return;
    let node = this.fastMap.get(scopeRef);
    if (!node) return;
    let parentNode = node.parent;
    for (let current of this.traverse()) if (current !== node && node.nodeToRestore && current.nodeToRestore && node.scopeRef && node.scopeRef.current && $9bf71ea28793e738$var$isElementInScope(current.nodeToRestore, node.scopeRef.current)) current.nodeToRestore = node.nodeToRestore;
    let children = node.children;
    if (parentNode) {
      parentNode.removeChild(node);
      if (children.size > 0) children.forEach((child) => parentNode && parentNode.addChild(child));
    }
    this.fastMap.delete(node.scopeRef);
  }
  // Pre Order Depth First
  *traverse(node = this.root) {
    if (node.scopeRef != null) yield node;
    if (node.children.size > 0) for (let child of node.children) yield* this.traverse(child);
  }
  clone() {
    var _node_parent;
    let newTree = new _$9bf71ea28793e738$var$Tree();
    var _node_parent_scopeRef;
    for (let node of this.traverse()) newTree.addTreeNode(node.scopeRef, (_node_parent_scopeRef = (_node_parent = node.parent) === null || _node_parent === void 0 ? void 0 : _node_parent.scopeRef) !== null && _node_parent_scopeRef !== void 0 ? _node_parent_scopeRef : null, node.nodeToRestore);
    return newTree;
  }
  constructor() {
    this.fastMap = /* @__PURE__ */ new Map();
    this.root = new $9bf71ea28793e738$var$TreeNode({
      scopeRef: null
    });
    this.fastMap.set(null, this.root);
  }
};
var $9bf71ea28793e738$var$TreeNode = class {
  addChild(node) {
    this.children.add(node);
    node.parent = this;
  }
  removeChild(node) {
    this.children.delete(node);
    node.parent = void 0;
  }
  constructor(props) {
    this.children = /* @__PURE__ */ new Set();
    this.contain = false;
    this.scopeRef = props.scopeRef;
  }
};
var $9bf71ea28793e738$export$d06fae2ee68b101e = new $9bf71ea28793e738$var$Tree();

// node_modules/@react-aria/focus/dist/useFocusRing.mjs
var import_react37 = __toESM(require_react(), 1);
function $f7dceffc5ad7768b$export$4e328f61c538687f(props = {}) {
  let { autoFocus = false, isTextInput, within } = props;
  let state = (0, import_react37.useRef)({
    isFocused: false,
    isFocusVisible: autoFocus || (0, $507fabe10e71c6fb$export$b9b3dfddab17db27)()
  });
  let [isFocused, setFocused] = (0, import_react37.useState)(false);
  let [isFocusVisibleState, setFocusVisible] = (0, import_react37.useState)(() => state.current.isFocused && state.current.isFocusVisible);
  let updateState = (0, import_react37.useCallback)(() => setFocusVisible(state.current.isFocused && state.current.isFocusVisible), []);
  let onFocusChange = (0, import_react37.useCallback)((isFocused2) => {
    state.current.isFocused = isFocused2;
    setFocused(isFocused2);
    updateState();
  }, [
    updateState
  ]);
  (0, $507fabe10e71c6fb$export$ec71b4b83ac08ec3)((isFocusVisible) => {
    state.current.isFocusVisible = isFocusVisible;
    updateState();
  }, [], {
    isTextInput
  });
  let { focusProps } = (0, $a1ea59d68270f0dd$export$f8168d8dd8fd66e6)({
    isDisabled: within,
    onFocusChange
  });
  let { focusWithinProps } = (0, $9ab94262bd0047c7$export$420e68273165f4ec)({
    isDisabled: !within,
    onFocusWithinChange: onFocusChange
  });
  return {
    isFocused,
    isFocusVisible: isFocusVisibleState,
    focusProps: within ? focusWithinProps : focusProps
  };
}

// node_modules/@react-aria/focus/dist/FocusRing.mjs
var import_react38 = __toESM(require_react(), 1);

// node_modules/@react-aria/focus/dist/useHasTabbableChild.mjs
var import_react39 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/components/button/button.js
var import_react47 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-active-press.js
var import_react44 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/env.js
var i = Object.defineProperty;
var d = (t12, e8, n13) => e8 in t12 ? i(t12, e8, { enumerable: true, configurable: true, writable: true, value: n13 }) : t12[e8] = n13;
var r = (t12, e8, n13) => (d(t12, typeof e8 != "symbol" ? e8 + "" : e8, n13), n13);
var o = class {
  constructor() {
    r(this, "current", this.detect());
    r(this, "handoffState", "pending");
    r(this, "currentId", 0);
  }
  set(e8) {
    this.current !== e8 && (this.handoffState = "pending", this.currentId = 0, this.current = e8);
  }
  reset() {
    this.set(this.detect());
  }
  nextId() {
    return ++this.currentId;
  }
  get isServer() {
    return this.current === "server";
  }
  get isClient() {
    return this.current === "client";
  }
  detect() {
    return typeof window == "undefined" || typeof document == "undefined" ? "server" : "client";
  }
  handoff() {
    this.handoffState === "pending" && (this.handoffState = "complete");
  }
  get isHandoffComplete() {
    return this.handoffState === "complete";
  }
};
var s = new o();

// node_modules/@headlessui/react/dist/utils/owner.js
function o2(n13) {
  var e8, r20;
  return s.isServer ? null : n13 ? "ownerDocument" in n13 ? n13.ownerDocument : "current" in n13 ? (r20 = (e8 = n13.current) == null ? void 0 : e8.ownerDocument) != null ? r20 : document : null : document;
}

// node_modules/@headlessui/react/dist/hooks/use-disposables.js
var import_react40 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/micro-task.js
function t(e8) {
  typeof queueMicrotask == "function" ? queueMicrotask(e8) : Promise.resolve().then(e8).catch((o21) => setTimeout(() => {
    throw o21;
  }));
}

// node_modules/@headlessui/react/dist/utils/disposables.js
function o3() {
  let n13 = [], r20 = { addEventListener(e8, t12, s15, a25) {
    return e8.addEventListener(t12, s15, a25), r20.add(() => e8.removeEventListener(t12, s15, a25));
  }, requestAnimationFrame(...e8) {
    let t12 = requestAnimationFrame(...e8);
    return r20.add(() => cancelAnimationFrame(t12));
  }, nextFrame(...e8) {
    return r20.requestAnimationFrame(() => r20.requestAnimationFrame(...e8));
  }, setTimeout(...e8) {
    let t12 = setTimeout(...e8);
    return r20.add(() => clearTimeout(t12));
  }, microTask(...e8) {
    let t12 = { current: true };
    return t(() => {
      t12.current && e8[0]();
    }), r20.add(() => {
      t12.current = false;
    });
  }, style(e8, t12, s15) {
    let a25 = e8.style.getPropertyValue(t12);
    return Object.assign(e8.style, { [t12]: s15 }), this.add(() => {
      Object.assign(e8.style, { [t12]: a25 });
    });
  }, group(e8) {
    let t12 = o3();
    return e8(t12), this.add(() => t12.dispose());
  }, add(e8) {
    return n13.includes(e8) || n13.push(e8), () => {
      let t12 = n13.indexOf(e8);
      if (t12 >= 0) for (let s15 of n13.splice(t12, 1)) s15();
    };
  }, dispose() {
    for (let e8 of n13.splice(0)) e8();
  } };
  return r20;
}

// node_modules/@headlessui/react/dist/hooks/use-disposables.js
function p() {
  let [e8] = (0, import_react40.useState)(o3);
  return (0, import_react40.useEffect)(() => () => e8.dispose(), [e8]), e8;
}

// node_modules/@headlessui/react/dist/hooks/use-event.js
var import_react43 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-latest-value.js
var import_react42 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-iso-morphic-effect.js
var import_react41 = __toESM(require_react(), 1);
var n = (e8, t12) => {
  s.isServer ? (0, import_react41.useEffect)(e8, t12) : (0, import_react41.useLayoutEffect)(e8, t12);
};

// node_modules/@headlessui/react/dist/hooks/use-latest-value.js
function s3(e8) {
  let r20 = (0, import_react42.useRef)(e8);
  return n(() => {
    r20.current = e8;
  }, [e8]), r20;
}

// node_modules/@headlessui/react/dist/hooks/use-event.js
var o5 = function(t12) {
  let e8 = s3(t12);
  return import_react43.default.useCallback((...r20) => e8.current(...r20), [e8]);
};

// node_modules/@headlessui/react/dist/hooks/use-active-press.js
function E(e8) {
  let t12 = e8.width / 2, n13 = e8.height / 2;
  return { top: e8.clientY - n13, right: e8.clientX + t12, bottom: e8.clientY + n13, left: e8.clientX - t12 };
}
function P(e8, t12) {
  return !(!e8 || !t12 || e8.right < t12.left || e8.left > t12.right || e8.bottom < t12.top || e8.top > t12.bottom);
}
function w({ disabled: e8 = false } = {}) {
  let t12 = (0, import_react44.useRef)(null), [n13, l17] = (0, import_react44.useState)(false), r20 = p(), o21 = o5(() => {
    t12.current = null, l17(false), r20.dispose();
  }), f22 = o5((s15) => {
    if (r20.dispose(), t12.current === null) {
      t12.current = s15.currentTarget, l17(true);
      {
        let i19 = o2(s15.currentTarget);
        r20.addEventListener(i19, "pointerup", o21, false), r20.addEventListener(i19, "pointermove", (c19) => {
          if (t12.current) {
            let p6 = E(c19);
            l17(P(p6, t12.current.getBoundingClientRect()));
          }
        }, false), r20.addEventListener(i19, "pointercancel", o21, false);
      }
    }
  });
  return { pressed: n13, pressProps: e8 ? {} : { onPointerDown: f22, onPointerUp: o21, onClick: o21 } };
}

// node_modules/@headlessui/react/dist/internal/disabled.js
var import_react45 = __toESM(require_react(), 1);
var e = (0, import_react45.createContext)(void 0);
function a3() {
  return (0, import_react45.useContext)(e);
}
function l({ value: t12, children: o21 }) {
  return import_react45.default.createElement(e.Provider, { value: t12 }, o21);
}

// node_modules/@headlessui/react/dist/utils/render.js
var import_react46 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/class-names.js
function t3(...r20) {
  return Array.from(new Set(r20.flatMap((n13) => typeof n13 == "string" ? n13.split(" ") : []))).filter(Boolean).join(" ");
}

// node_modules/@headlessui/react/dist/utils/match.js
function u(r20, n13, ...a25) {
  if (r20 in n13) {
    let e8 = n13[r20];
    return typeof e8 == "function" ? e8(...a25) : e8;
  }
  let t12 = new Error(`Tried to handle "${r20}" but there is no handler defined. Only defined handlers are: ${Object.keys(n13).map((e8) => `"${e8}"`).join(", ")}.`);
  throw Error.captureStackTrace && Error.captureStackTrace(t12, u), t12;
}

// node_modules/@headlessui/react/dist/utils/render.js
var O = ((a25) => (a25[a25.None = 0] = "None", a25[a25.RenderStrategy = 1] = "RenderStrategy", a25[a25.Static = 2] = "Static", a25))(O || {});
var A = ((e8) => (e8[e8.Unmount = 0] = "Unmount", e8[e8.Hidden = 1] = "Hidden", e8))(A || {});
function L() {
  let n13 = U();
  return (0, import_react46.useCallback)((r20) => C({ mergeRefs: n13, ...r20 }), [n13]);
}
function C({ ourProps: n13, theirProps: r20, slot: e8, defaultTag: a25, features: s15, visible: t12 = true, name: l17, mergeRefs: i19 }) {
  i19 = i19 != null ? i19 : $;
  let o21 = P2(r20, n13);
  if (t12) return F(o21, e8, a25, l17, i19);
  let y8 = s15 != null ? s15 : 0;
  if (y8 & 2) {
    let { static: f22 = false, ...u19 } = o21;
    if (f22) return F(u19, e8, a25, l17, i19);
  }
  if (y8 & 1) {
    let { unmount: f22 = true, ...u19 } = o21;
    return u(f22 ? 0 : 1, { [0]() {
      return null;
    }, [1]() {
      return F({ ...u19, hidden: true, style: { display: "none" } }, e8, a25, l17, i19);
    } });
  }
  return F(o21, e8, a25, l17, i19);
}
function F(n13, r20 = {}, e8, a25, s15) {
  let { as: t12 = e8, children: l17, refName: i19 = "ref", ...o21 } = h(n13, ["unmount", "static"]), y8 = n13.ref !== void 0 ? { [i19]: n13.ref } : {}, f22 = typeof l17 == "function" ? l17(r20) : l17;
  "className" in o21 && o21.className && typeof o21.className == "function" && (o21.className = o21.className(r20)), o21["aria-labelledby"] && o21["aria-labelledby"] === o21.id && (o21["aria-labelledby"] = void 0);
  let u19 = {};
  if (r20) {
    let d14 = false, p6 = [];
    for (let [c19, T12] of Object.entries(r20)) typeof T12 == "boolean" && (d14 = true), T12 === true && p6.push(c19.replace(/([A-Z])/g, (g8) => `-${g8.toLowerCase()}`));
    if (d14) {
      u19["data-headlessui-state"] = p6.join(" ");
      for (let c19 of p6) u19[`data-${c19}`] = "";
    }
  }
  if (t12 === import_react46.Fragment && (Object.keys(m2(o21)).length > 0 || Object.keys(m2(u19)).length > 0)) if (!(0, import_react46.isValidElement)(f22) || Array.isArray(f22) && f22.length > 1) {
    if (Object.keys(m2(o21)).length > 0) throw new Error(['Passing props on "Fragment"!', "", `The current component <${a25} /> is rendering a "Fragment".`, "However we need to passthrough the following props:", Object.keys(m2(o21)).concat(Object.keys(m2(u19))).map((d14) => `  - ${d14}`).join(`
`), "", "You can apply a few solutions:", ['Add an `as="..."` prop, to ensure that we render an actual element instead of a "Fragment".', "Render a single element as the child so that we can forward the props onto that element."].map((d14) => `  - ${d14}`).join(`
`)].join(`
`));
  } else {
    let d14 = f22.props, p6 = d14 == null ? void 0 : d14.className, c19 = typeof p6 == "function" ? (...R9) => t3(p6(...R9), o21.className) : t3(p6, o21.className), T12 = c19 ? { className: c19 } : {}, g8 = P2(f22.props, m2(h(o21, ["ref"])));
    for (let R9 in u19) R9 in g8 && delete u19[R9];
    return (0, import_react46.cloneElement)(f22, Object.assign({}, g8, u19, y8, { ref: s15(H(f22), y8.ref) }, T12));
  }
  return (0, import_react46.createElement)(t12, Object.assign({}, h(o21, ["ref"]), t12 !== import_react46.Fragment && y8, t12 !== import_react46.Fragment && u19), f22);
}
function U() {
  let n13 = (0, import_react46.useRef)([]), r20 = (0, import_react46.useCallback)((e8) => {
    for (let a25 of n13.current) a25 != null && (typeof a25 == "function" ? a25(e8) : a25.current = e8);
  }, []);
  return (...e8) => {
    if (!e8.every((a25) => a25 == null)) return n13.current = e8, r20;
  };
}
function $(...n13) {
  return n13.every((r20) => r20 == null) ? void 0 : (r20) => {
    for (let e8 of n13) e8 != null && (typeof e8 == "function" ? e8(r20) : e8.current = r20);
  };
}
function P2(...n13) {
  var a25;
  if (n13.length === 0) return {};
  if (n13.length === 1) return n13[0];
  let r20 = {}, e8 = {};
  for (let s15 of n13) for (let t12 in s15) t12.startsWith("on") && typeof s15[t12] == "function" ? ((a25 = e8[t12]) != null || (e8[t12] = []), e8[t12].push(s15[t12])) : r20[t12] = s15[t12];
  if (r20.disabled || r20["aria-disabled"]) for (let s15 in e8) /^(on(?:Click|Pointer|Mouse|Key)(?:Down|Up|Press)?)$/.test(s15) && (e8[s15] = [(t12) => {
    var l17;
    return (l17 = t12 == null ? void 0 : t12.preventDefault) == null ? void 0 : l17.call(t12);
  }]);
  for (let s15 in e8) Object.assign(r20, { [s15](t12, ...l17) {
    let i19 = e8[s15];
    for (let o21 of i19) {
      if ((t12 instanceof Event || (t12 == null ? void 0 : t12.nativeEvent) instanceof Event) && t12.defaultPrevented) return;
      o21(t12, ...l17);
    }
  } });
  return r20;
}
function _(...n13) {
  var a25;
  if (n13.length === 0) return {};
  if (n13.length === 1) return n13[0];
  let r20 = {}, e8 = {};
  for (let s15 of n13) for (let t12 in s15) t12.startsWith("on") && typeof s15[t12] == "function" ? ((a25 = e8[t12]) != null || (e8[t12] = []), e8[t12].push(s15[t12])) : r20[t12] = s15[t12];
  for (let s15 in e8) Object.assign(r20, { [s15](...t12) {
    let l17 = e8[s15];
    for (let i19 of l17) i19 == null || i19(...t12);
  } });
  return r20;
}
function K(n13) {
  var r20;
  return Object.assign((0, import_react46.forwardRef)(n13), { displayName: (r20 = n13.displayName) != null ? r20 : n13.name });
}
function m2(n13) {
  let r20 = Object.assign({}, n13);
  for (let e8 in r20) r20[e8] === void 0 && delete r20[e8];
  return r20;
}
function h(n13, r20 = []) {
  let e8 = Object.assign({}, n13);
  for (let a25 of r20) a25 in e8 && delete e8[a25];
  return e8;
}
function H(n13) {
  return import_react46.default.version.split(".")[0] >= "19" ? n13.props.ref : n13.ref;
}

// node_modules/@headlessui/react/dist/components/button/button.js
var R = "button";
function v2(a25, u19) {
  var p6;
  let l17 = a3(), { disabled: e8 = l17 || false, autoFocus: t12 = false, ...o21 } = a25, { isFocusVisible: r20, focusProps: i19 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: t12 }), { isHovered: s15, hoverProps: T12 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), { pressed: n13, pressProps: d14 } = w({ disabled: e8 }), f22 = _({ ref: u19, type: (p6 = o21.type) != null ? p6 : "button", disabled: e8 || void 0, autoFocus: t12 }, i19, T12, d14), m11 = (0, import_react47.useMemo)(() => ({ disabled: e8, hover: s15, focus: r20, active: n13, autofocus: t12 }), [e8, s15, r20, n13, t12]);
  return L()({ ourProps: f22, theirProps: o21, slot: m11, defaultTag: R, name: "Button" });
}
var H2 = K(v2);

// node_modules/@headlessui/react/dist/components/checkbox/checkbox.js
var import_react56 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-controllable.js
var import_react48 = __toESM(require_react(), 1);
function T(l17, r20, c19) {
  let [i19, s15] = (0, import_react48.useState)(c19), e8 = l17 !== void 0, t12 = (0, import_react48.useRef)(e8), u19 = (0, import_react48.useRef)(false), d14 = (0, import_react48.useRef)(false);
  return e8 && !t12.current && !u19.current ? (u19.current = true, t12.current = e8, console.error("A component is changing from uncontrolled to controlled. This may be caused by the value changing from undefined to a defined value, which should not happen.")) : !e8 && t12.current && !d14.current && (d14.current = true, t12.current = e8, console.error("A component is changing from controlled to uncontrolled. This may be caused by the value changing from a defined value to undefined, which should not happen.")), [e8 ? l17 : i19, o5((n13) => (e8 || s15(n13), r20 == null ? void 0 : r20(n13)))];
}

// node_modules/@headlessui/react/dist/hooks/use-default-value.js
var import_react49 = __toESM(require_react(), 1);
function l2(e8) {
  let [t12] = (0, import_react49.useState)(e8);
  return t12;
}

// node_modules/@headlessui/react/dist/hooks/use-id.js
var import_react50 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/internal/form-fields.js
var import_react51 = __toESM(require_react(), 1);
var import_react_dom3 = __toESM(require_react_dom(), 1);

// node_modules/@headlessui/react/dist/utils/form.js
function e2(i19 = {}, s15 = null, t12 = []) {
  for (let [r20, n13] of Object.entries(i19)) o7(t12, f3(s15, r20), n13);
  return t12;
}
function f3(i19, s15) {
  return i19 ? i19 + "[" + s15 + "]" : s15;
}
function o7(i19, s15, t12) {
  if (Array.isArray(t12)) for (let [r20, n13] of t12.entries()) o7(i19, f3(s15, r20.toString()), n13);
  else t12 instanceof Date ? i19.push([s15, t12.toISOString()]) : typeof t12 == "boolean" ? i19.push([s15, t12 ? "1" : "0"]) : typeof t12 == "string" ? i19.push([s15, t12]) : typeof t12 == "number" ? i19.push([s15, `${t12}`]) : t12 == null ? i19.push([s15, ""]) : e2(t12, s15, i19);
}
function p2(i19) {
  var t12, r20;
  let s15 = (t12 = i19 == null ? void 0 : i19.form) != null ? t12 : i19.closest("form");
  if (s15) {
    for (let n13 of s15.elements) if (n13 !== i19 && (n13.tagName === "INPUT" && n13.type === "submit" || n13.tagName === "BUTTON" && n13.type === "submit" || n13.nodeName === "INPUT" && n13.type === "image")) {
      n13.click();
      return;
    }
    (r20 = s15.requestSubmit) == null || r20.call(s15);
  }
}

// node_modules/@headlessui/react/dist/internal/hidden.js
var a4 = "span";
var s4 = ((e8) => (e8[e8.None = 1] = "None", e8[e8.Focusable = 2] = "Focusable", e8[e8.Hidden = 4] = "Hidden", e8))(s4 || {});
function l3(t12, r20) {
  var n13;
  let { features: d14 = 1, ...e8 } = t12, o21 = { ref: r20, "aria-hidden": (d14 & 2) === 2 ? true : (n13 = e8["aria-hidden"]) != null ? n13 : void 0, hidden: (d14 & 4) === 4 ? true : void 0, style: { position: "fixed", top: 1, left: 1, width: 1, height: 0, padding: 0, margin: -1, overflow: "hidden", clip: "rect(0, 0, 0, 0)", whiteSpace: "nowrap", borderWidth: "0", ...(d14 & 4) === 4 && (d14 & 2) !== 2 && { display: "none" } } };
  return L()({ ourProps: o21, theirProps: e8, slot: {}, defaultTag: a4, name: "Hidden" });
}
var f4 = K(l3);

// node_modules/@headlessui/react/dist/internal/form-fields.js
var f5 = (0, import_react51.createContext)(null);
function W(t12) {
  let [e8, r20] = (0, import_react51.useState)(null);
  return import_react51.default.createElement(f5.Provider, { value: { target: e8 } }, t12.children, import_react51.default.createElement(f4, { features: s4.Hidden, ref: r20 }));
}
function c2({ children: t12 }) {
  let e8 = (0, import_react51.useContext)(f5);
  if (!e8) return import_react51.default.createElement(import_react51.default.Fragment, null, t12);
  let { target: r20 } = e8;
  return r20 ? (0, import_react_dom3.createPortal)(import_react51.default.createElement(import_react51.default.Fragment, null, t12), r20) : null;
}
function j2({ data: t12, form: e8, disabled: r20, onReset: n13, overrides: F9 }) {
  let [i19, a25] = (0, import_react51.useState)(null), p6 = p();
  return (0, import_react51.useEffect)(() => {
    if (n13 && i19) return p6.addEventListener(i19, "reset", n13);
  }, [i19, e8, n13]), import_react51.default.createElement(c2, null, import_react51.default.createElement(C2, { setForm: a25, formId: e8 }), e2(t12).map(([s15, v5]) => import_react51.default.createElement(f4, { features: s4.Hidden, ...m2({ key: s15, as: "input", type: "hidden", hidden: true, readOnly: true, form: e8, disabled: r20, name: s15, value: v5, ...F9 }) })));
}
function C2({ setForm: t12, formId: e8 }) {
  return (0, import_react51.useEffect)(() => {
    if (e8) {
      let r20 = document.getElementById(e8);
      r20 && t12(r20);
    }
  }, [t12, e8]), e8 ? null : import_react51.default.createElement(f4, { features: s4.Hidden, as: "input", type: "hidden", hidden: true, readOnly: true, ref: (r20) => {
    if (!r20) return;
    let n13 = r20.closest("form");
    n13 && t12(n13);
  } });
}

// node_modules/@headlessui/react/dist/internal/id.js
var import_react52 = __toESM(require_react(), 1);
var e3 = (0, import_react52.createContext)(void 0);
function u4() {
  return (0, import_react52.useContext)(e3);
}
function f6({ id: t12, children: r20 }) {
  return import_react52.default.createElement(e3.Provider, { value: t12 }, r20);
}

// node_modules/@headlessui/react/dist/utils/bugs.js
function r4(n13) {
  let e8 = n13.parentElement, l17 = null;
  for (; e8 && !(e8 instanceof HTMLFieldSetElement); ) e8 instanceof HTMLLegendElement && (l17 = e8), e8 = e8.parentElement;
  let t12 = (e8 == null ? void 0 : e8.getAttribute("disabled")) === "";
  return t12 && i4(l17) ? false : t12;
}
function i4(n13) {
  if (!n13) return false;
  let e8 = n13.previousElementSibling;
  for (; e8 !== null; ) {
    if (e8 instanceof HTMLLegendElement) return false;
    e8 = e8.previousElementSibling;
  }
  return true;
}

// node_modules/@headlessui/react/dist/components/description/description.js
var import_react54 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-sync-refs.js
var import_react53 = __toESM(require_react(), 1);
var u5 = Symbol();
function T2(t12, n13 = true) {
  return Object.assign(t12, { [u5]: n13 });
}
function y(...t12) {
  let n13 = (0, import_react53.useRef)(t12);
  (0, import_react53.useEffect)(() => {
    n13.current = t12;
  }, [t12]);
  let c19 = o5((e8) => {
    for (let o21 of n13.current) o21 != null && (typeof o21 == "function" ? o21(e8) : o21.current = e8);
  });
  return t12.every((e8) => e8 == null || (e8 == null ? void 0 : e8[u5])) ? void 0 : c19;
}

// node_modules/@headlessui/react/dist/components/description/description.js
var a5 = (0, import_react54.createContext)(null);
a5.displayName = "DescriptionContext";
function f7() {
  let r20 = (0, import_react54.useContext)(a5);
  if (r20 === null) {
    let e8 = new Error("You used a <Description /> component, but it is not inside a relevant parent.");
    throw Error.captureStackTrace && Error.captureStackTrace(e8, f7), e8;
  }
  return r20;
}
function U2() {
  var r20, e8;
  return (e8 = (r20 = (0, import_react54.useContext)(a5)) == null ? void 0 : r20.value) != null ? e8 : void 0;
}
function w3() {
  let [r20, e8] = (0, import_react54.useState)([]);
  return [r20.length > 0 ? r20.join(" ") : void 0, (0, import_react54.useMemo)(() => function(t12) {
    let i19 = o5((n13) => (e8((s15) => [...s15, n13]), () => e8((s15) => {
      let o21 = s15.slice(), p6 = o21.indexOf(n13);
      return p6 !== -1 && o21.splice(p6, 1), o21;
    }))), l17 = (0, import_react54.useMemo)(() => ({ register: i19, slot: t12.slot, name: t12.name, props: t12.props, value: t12.value }), [i19, t12.slot, t12.name, t12.props, t12.value]);
    return import_react54.default.createElement(a5.Provider, { value: l17 }, t12.children);
  }, [e8])];
}
var S2 = "p";
function C3(r20, e8) {
  let d14 = (0, import_react50.useId)(), t12 = a3(), { id: i19 = `headlessui-description-${d14}`, ...l17 } = r20, n13 = f7(), s15 = y(e8);
  n(() => n13.register(i19), [i19, n13.register]);
  let o21 = t12 || false, p6 = (0, import_react54.useMemo)(() => ({ ...n13.slot, disabled: o21 }), [n13.slot, o21]), D8 = { ref: s15, ...n13.props, id: i19 };
  return L()({ ourProps: D8, theirProps: l17, slot: p6, defaultTag: S2, name: n13.name || "Description" });
}
var _2 = K(C3);
var H4 = Object.assign(_2, {});

// node_modules/@headlessui/react/dist/components/keyboard.js
var o9 = ((r20) => (r20.Space = " ", r20.Enter = "Enter", r20.Escape = "Escape", r20.Backspace = "Backspace", r20.Delete = "Delete", r20.ArrowLeft = "ArrowLeft", r20.ArrowUp = "ArrowUp", r20.ArrowRight = "ArrowRight", r20.ArrowDown = "ArrowDown", r20.Home = "Home", r20.End = "End", r20.PageUp = "PageUp", r20.PageDown = "PageDown", r20.Tab = "Tab", r20))(o9 || {});

// node_modules/@headlessui/react/dist/components/label/label.js
var import_react55 = __toESM(require_react(), 1);
var c4 = (0, import_react55.createContext)(null);
c4.displayName = "LabelContext";
function P5() {
  let r20 = (0, import_react55.useContext)(c4);
  if (r20 === null) {
    let l17 = new Error("You used a <Label /> component, but it is not inside a relevant parent.");
    throw Error.captureStackTrace && Error.captureStackTrace(l17, P5), l17;
  }
  return r20;
}
function I(r20) {
  var a25, e8, o21;
  let l17 = (e8 = (a25 = (0, import_react55.useContext)(c4)) == null ? void 0 : a25.value) != null ? e8 : void 0;
  return ((o21 = r20 == null ? void 0 : r20.length) != null ? o21 : 0) > 0 ? [l17, ...r20].filter(Boolean).join(" ") : l17;
}
function K2({ inherit: r20 = false } = {}) {
  let l17 = I(), [a25, e8] = (0, import_react55.useState)([]), o21 = r20 ? [l17, ...a25].filter(Boolean) : a25;
  return [o21.length > 0 ? o21.join(" ") : void 0, (0, import_react55.useMemo)(() => function(t12) {
    let s15 = o5((i19) => (e8((p6) => [...p6, i19]), () => e8((p6) => {
      let u19 = p6.slice(), d14 = u19.indexOf(i19);
      return d14 !== -1 && u19.splice(d14, 1), u19;
    }))), m11 = (0, import_react55.useMemo)(() => ({ register: s15, slot: t12.slot, name: t12.name, props: t12.props, value: t12.value }), [s15, t12.slot, t12.name, t12.props, t12.value]);
    return import_react55.default.createElement(c4.Provider, { value: m11 }, t12.children);
  }, [e8])];
}
var N = "label";
function G(r20, l17) {
  var y8;
  let a25 = (0, import_react50.useId)(), e8 = P5(), o21 = u4(), g8 = a3(), { id: t12 = `headlessui-label-${a25}`, htmlFor: s15 = o21 != null ? o21 : (y8 = e8.props) == null ? void 0 : y8.htmlFor, passive: m11 = false, ...i19 } = r20, p6 = y(l17);
  n(() => e8.register(t12), [t12, e8.register]);
  let u19 = o5((L7) => {
    let b11 = L7.currentTarget;
    if (b11 instanceof HTMLLabelElement && L7.preventDefault(), e8.props && "onClick" in e8.props && typeof e8.props.onClick == "function" && e8.props.onClick(L7), b11 instanceof HTMLLabelElement) {
      let n13 = document.getElementById(b11.htmlFor);
      if (n13) {
        let E15 = n13.getAttribute("disabled");
        if (E15 === "true" || E15 === "") return;
        let x11 = n13.getAttribute("aria-disabled");
        if (x11 === "true" || x11 === "") return;
        (n13 instanceof HTMLInputElement && (n13.type === "radio" || n13.type === "checkbox") || n13.role === "radio" || n13.role === "checkbox" || n13.role === "switch") && n13.click(), n13.focus({ preventScroll: true });
      }
    }
  }), d14 = g8 || false, C10 = (0, import_react55.useMemo)(() => ({ ...e8.slot, disabled: d14 }), [e8.slot, d14]), f22 = { ref: p6, ...e8.props, id: t12, htmlFor: s15, onClick: u19 };
  return m11 && ("onClick" in f22 && (delete f22.htmlFor, delete f22.onClick), "onClick" in i19 && delete i19.onClick), L()({ ourProps: f22, theirProps: i19, slot: C10, defaultTag: s15 ? N : "div", name: e8.name || "Label" });
}
var U3 = K(G);
var Q = Object.assign(U3, {});

// node_modules/@headlessui/react/dist/components/checkbox/checkbox.js
var de = "span";
function pe(T12, h11) {
  let C10 = (0, import_react50.useId)(), k5 = u4(), x11 = a3(), { id: g8 = k5 || `headlessui-checkbox-${C10}`, disabled: e8 = x11 || false, autoFocus: s15 = false, checked: E15, defaultChecked: v5, onChange: P7, name: d14, value: D8, form: R9, indeterminate: n13 = false, tabIndex: A8 = 0, ...F9 } = T12, r20 = l2(v5), [a25, t12] = T(E15, P7, r20 != null ? r20 : false), K4 = I(), _8 = U2(), H14 = p(), [p6, c19] = (0, import_react56.useState)(false), u19 = o5(() => {
    c19(true), t12 == null || t12(!a25), H14.nextFrame(() => {
      c19(false);
    });
  }), B5 = o5((o21) => {
    if (r4(o21.currentTarget)) return o21.preventDefault();
    o21.preventDefault(), u19();
  }), I7 = o5((o21) => {
    o21.key === o9.Space ? (o21.preventDefault(), u19()) : o21.key === o9.Enter && p2(o21.currentTarget);
  }), L7 = o5((o21) => o21.preventDefault()), { isFocusVisible: m11, focusProps: M11 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: s15 }), { isHovered: b11, hoverProps: U7 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), { pressed: f22, pressProps: O9 } = w({ disabled: e8 }), X6 = _({ ref: h11, id: g8, role: "checkbox", "aria-checked": n13 ? "mixed" : a25 ? "true" : "false", "aria-labelledby": K4, "aria-describedby": _8, "aria-disabled": e8 ? true : void 0, indeterminate: n13 ? "true" : void 0, tabIndex: e8 ? void 0 : A8, onKeyUp: e8 ? void 0 : I7, onKeyPress: e8 ? void 0 : L7, onClick: e8 ? void 0 : B5 }, M11, U7, O9), G8 = (0, import_react56.useMemo)(() => ({ checked: a25, disabled: e8, hover: b11, focus: m11, active: f22, indeterminate: n13, changing: p6, autofocus: s15 }), [a25, n13, e8, b11, m11, f22, p6, s15]), S10 = (0, import_react56.useCallback)(() => {
    if (r20 !== void 0) return t12 == null ? void 0 : t12(r20);
  }, [t12, r20]), W3 = L();
  return import_react56.default.createElement(import_react56.default.Fragment, null, d14 != null && import_react56.default.createElement(j2, { disabled: e8, data: { [d14]: D8 || "on" }, overrides: { type: "checkbox", checked: a25 }, form: R9, onReset: S10 }), W3({ ourProps: X6, theirProps: F9, slot: G8, defaultTag: de, name: "Checkbox" }));
}
var Fe = K(pe);

// node_modules/@headlessui/react/dist/components/close-button/close-button.js
var import_react58 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/internal/close-provider.js
var import_react57 = __toESM(require_react(), 1);
var e4 = (0, import_react57.createContext)(() => {
});
function u7() {
  return (0, import_react57.useContext)(e4);
}
function C4({ value: t12, children: o21 }) {
  return import_react57.default.createElement(e4.Provider, { value: t12 }, o21);
}

// node_modules/@headlessui/react/dist/components/close-button/close-button.js
function l5(t12, e8) {
  let o21 = u7();
  return import_react58.default.createElement(H2, { ref: e8, ..._({ onClick: o21 }, t12) });
}
var y2 = K(l5);

// node_modules/@tanstack/react-virtual/dist/esm/index.js
var React = __toESM(require_react());
var import_react_dom4 = __toESM(require_react_dom());

// node_modules/@tanstack/virtual-core/dist/esm/utils.js
function memo(getDeps, fn, opts) {
  let deps = opts.initialDeps ?? [];
  let result;
  function memoizedFunction() {
    var _a, _b, _c, _d;
    let depTime;
    if (opts.key && ((_a = opts.debug) == null ? void 0 : _a.call(opts))) depTime = Date.now();
    const newDeps = getDeps();
    const depsChanged = newDeps.length !== deps.length || newDeps.some((dep, index3) => deps[index3] !== dep);
    if (!depsChanged) {
      return result;
    }
    deps = newDeps;
    let resultTime;
    if (opts.key && ((_b = opts.debug) == null ? void 0 : _b.call(opts))) resultTime = Date.now();
    result = fn(...newDeps);
    if (opts.key && ((_c = opts.debug) == null ? void 0 : _c.call(opts))) {
      const depEndTime = Math.round((Date.now() - depTime) * 100) / 100;
      const resultEndTime = Math.round((Date.now() - resultTime) * 100) / 100;
      const resultFpsPercentage = resultEndTime / 16;
      const pad = (str, num) => {
        str = String(str);
        while (str.length < num) {
          str = " " + str;
        }
        return str;
      };
      console.info(
        `%c⏱ ${pad(resultEndTime, 5)} /${pad(depEndTime, 5)} ms`,
        `
            font-size: .6rem;
            font-weight: bold;
            color: hsl(${Math.max(
          0,
          Math.min(120 - 120 * resultFpsPercentage, 120)
        )}deg 100% 31%);`,
        opts == null ? void 0 : opts.key
      );
    }
    (_d = opts == null ? void 0 : opts.onChange) == null ? void 0 : _d.call(opts, result);
    return result;
  }
  memoizedFunction.updateDeps = (newDeps) => {
    deps = newDeps;
  };
  return memoizedFunction;
}
function notUndefined(value, msg) {
  if (value === void 0) {
    throw new Error(`Unexpected undefined${msg ? `: ${msg}` : ""}`);
  } else {
    return value;
  }
}
var approxEqual = (a25, b11) => Math.abs(a25 - b11) < 1;
var debounce = (targetWindow, fn, ms) => {
  let timeoutId2;
  return function(...args) {
    targetWindow.clearTimeout(timeoutId2);
    timeoutId2 = targetWindow.setTimeout(() => fn.apply(this, args), ms);
  };
};

// node_modules/@tanstack/virtual-core/dist/esm/index.js
var defaultKeyExtractor = (index3) => index3;
var defaultRangeExtractor = (range) => {
  const start = Math.max(range.startIndex - range.overscan, 0);
  const end = Math.min(range.endIndex + range.overscan, range.count - 1);
  const arr = [];
  for (let i19 = start; i19 <= end; i19++) {
    arr.push(i19);
  }
  return arr;
};
var observeElementRect = (instance, cb) => {
  const element = instance.scrollElement;
  if (!element) {
    return;
  }
  const targetWindow = instance.targetWindow;
  if (!targetWindow) {
    return;
  }
  const handler = (rect) => {
    const { width, height } = rect;
    cb({ width: Math.round(width), height: Math.round(height) });
  };
  handler(element.getBoundingClientRect());
  if (!targetWindow.ResizeObserver) {
    return () => {
    };
  }
  const observer = new targetWindow.ResizeObserver((entries) => {
    const run = () => {
      const entry = entries[0];
      if (entry == null ? void 0 : entry.borderBoxSize) {
        const box = entry.borderBoxSize[0];
        if (box) {
          handler({ width: box.inlineSize, height: box.blockSize });
          return;
        }
      }
      handler(element.getBoundingClientRect());
    };
    instance.options.useAnimationFrameWithResizeObserver ? requestAnimationFrame(run) : run();
  });
  observer.observe(element, { box: "border-box" });
  return () => {
    observer.unobserve(element);
  };
};
var addEventListenerOptions = {
  passive: true
};
var supportsScrollend = typeof window == "undefined" ? true : "onscrollend" in window;
var observeElementOffset = (instance, cb) => {
  const element = instance.scrollElement;
  if (!element) {
    return;
  }
  const targetWindow = instance.targetWindow;
  if (!targetWindow) {
    return;
  }
  let offset4 = 0;
  const fallback = instance.options.useScrollendEvent && supportsScrollend ? () => void 0 : debounce(
    targetWindow,
    () => {
      cb(offset4, false);
    },
    instance.options.isScrollingResetDelay
  );
  const createHandler = (isScrolling) => () => {
    const { horizontal, isRtl } = instance.options;
    offset4 = horizontal ? element["scrollLeft"] * (isRtl && -1 || 1) : element["scrollTop"];
    fallback();
    cb(offset4, isScrolling);
  };
  const handler = createHandler(true);
  const endHandler = createHandler(false);
  endHandler();
  element.addEventListener("scroll", handler, addEventListenerOptions);
  const registerScrollendEvent = instance.options.useScrollendEvent && supportsScrollend;
  if (registerScrollendEvent) {
    element.addEventListener("scrollend", endHandler, addEventListenerOptions);
  }
  return () => {
    element.removeEventListener("scroll", handler);
    if (registerScrollendEvent) {
      element.removeEventListener("scrollend", endHandler);
    }
  };
};
var measureElement = (element, entry, instance) => {
  if (entry == null ? void 0 : entry.borderBoxSize) {
    const box = entry.borderBoxSize[0];
    if (box) {
      const size4 = Math.round(
        box[instance.options.horizontal ? "inlineSize" : "blockSize"]
      );
      return size4;
    }
  }
  return Math.round(
    element.getBoundingClientRect()[instance.options.horizontal ? "width" : "height"]
  );
};
var elementScroll = (offset4, {
  adjustments = 0,
  behavior
}, instance) => {
  var _a, _b;
  const toOffset = offset4 + adjustments;
  (_b = (_a = instance.scrollElement) == null ? void 0 : _a.scrollTo) == null ? void 0 : _b.call(_a, {
    [instance.options.horizontal ? "left" : "top"]: toOffset,
    behavior
  });
};
var Virtualizer = class {
  constructor(opts) {
    this.unsubs = [];
    this.scrollElement = null;
    this.targetWindow = null;
    this.isScrolling = false;
    this.scrollToIndexTimeoutId = null;
    this.measurementsCache = [];
    this.itemSizeCache = /* @__PURE__ */ new Map();
    this.pendingMeasuredCacheIndexes = [];
    this.scrollRect = null;
    this.scrollOffset = null;
    this.scrollDirection = null;
    this.scrollAdjustments = 0;
    this.elementsCache = /* @__PURE__ */ new Map();
    this.observer = /* @__PURE__ */ (() => {
      let _ro = null;
      const get = () => {
        if (_ro) {
          return _ro;
        }
        if (!this.targetWindow || !this.targetWindow.ResizeObserver) {
          return null;
        }
        return _ro = new this.targetWindow.ResizeObserver((entries) => {
          entries.forEach((entry) => {
            const run = () => {
              this._measureElement(entry.target, entry);
            };
            this.options.useAnimationFrameWithResizeObserver ? requestAnimationFrame(run) : run();
          });
        });
      };
      return {
        disconnect: () => {
          var _a;
          (_a = get()) == null ? void 0 : _a.disconnect();
          _ro = null;
        },
        observe: (target) => {
          var _a;
          return (_a = get()) == null ? void 0 : _a.observe(target, { box: "border-box" });
        },
        unobserve: (target) => {
          var _a;
          return (_a = get()) == null ? void 0 : _a.unobserve(target);
        }
      };
    })();
    this.range = null;
    this.setOptions = (opts2) => {
      Object.entries(opts2).forEach(([key, value]) => {
        if (typeof value === "undefined") delete opts2[key];
      });
      this.options = {
        debug: false,
        initialOffset: 0,
        overscan: 1,
        paddingStart: 0,
        paddingEnd: 0,
        scrollPaddingStart: 0,
        scrollPaddingEnd: 0,
        horizontal: false,
        getItemKey: defaultKeyExtractor,
        rangeExtractor: defaultRangeExtractor,
        onChange: () => {
        },
        measureElement,
        initialRect: { width: 0, height: 0 },
        scrollMargin: 0,
        gap: 0,
        indexAttribute: "data-index",
        initialMeasurementsCache: [],
        lanes: 1,
        isScrollingResetDelay: 150,
        enabled: true,
        isRtl: false,
        useScrollendEvent: false,
        useAnimationFrameWithResizeObserver: false,
        ...opts2
      };
    };
    this.notify = (sync) => {
      var _a, _b;
      (_b = (_a = this.options).onChange) == null ? void 0 : _b.call(_a, this, sync);
    };
    this.maybeNotify = memo(
      () => {
        this.calculateRange();
        return [
          this.isScrolling,
          this.range ? this.range.startIndex : null,
          this.range ? this.range.endIndex : null
        ];
      },
      (isScrolling) => {
        this.notify(isScrolling);
      },
      {
        key: "maybeNotify",
        debug: () => this.options.debug,
        initialDeps: [
          this.isScrolling,
          this.range ? this.range.startIndex : null,
          this.range ? this.range.endIndex : null
        ]
      }
    );
    this.cleanup = () => {
      this.unsubs.filter(Boolean).forEach((d14) => d14());
      this.unsubs = [];
      this.observer.disconnect();
      this.scrollElement = null;
      this.targetWindow = null;
    };
    this._didMount = () => {
      return () => {
        this.cleanup();
      };
    };
    this._willUpdate = () => {
      var _a;
      const scrollElement = this.options.enabled ? this.options.getScrollElement() : null;
      if (this.scrollElement !== scrollElement) {
        this.cleanup();
        if (!scrollElement) {
          this.maybeNotify();
          return;
        }
        this.scrollElement = scrollElement;
        if (this.scrollElement && "ownerDocument" in this.scrollElement) {
          this.targetWindow = this.scrollElement.ownerDocument.defaultView;
        } else {
          this.targetWindow = ((_a = this.scrollElement) == null ? void 0 : _a.window) ?? null;
        }
        this.elementsCache.forEach((cached) => {
          this.observer.observe(cached);
        });
        this._scrollToOffset(this.getScrollOffset(), {
          adjustments: void 0,
          behavior: void 0
        });
        this.unsubs.push(
          this.options.observeElementRect(this, (rect) => {
            this.scrollRect = rect;
            this.maybeNotify();
          })
        );
        this.unsubs.push(
          this.options.observeElementOffset(this, (offset4, isScrolling) => {
            this.scrollAdjustments = 0;
            this.scrollDirection = isScrolling ? this.getScrollOffset() < offset4 ? "forward" : "backward" : null;
            this.scrollOffset = offset4;
            this.isScrolling = isScrolling;
            this.maybeNotify();
          })
        );
      }
    };
    this.getSize = () => {
      if (!this.options.enabled) {
        this.scrollRect = null;
        return 0;
      }
      this.scrollRect = this.scrollRect ?? this.options.initialRect;
      return this.scrollRect[this.options.horizontal ? "width" : "height"];
    };
    this.getScrollOffset = () => {
      if (!this.options.enabled) {
        this.scrollOffset = null;
        return 0;
      }
      this.scrollOffset = this.scrollOffset ?? (typeof this.options.initialOffset === "function" ? this.options.initialOffset() : this.options.initialOffset);
      return this.scrollOffset;
    };
    this.getFurthestMeasurement = (measurements, index3) => {
      const furthestMeasurementsFound = /* @__PURE__ */ new Map();
      const furthestMeasurements = /* @__PURE__ */ new Map();
      for (let m11 = index3 - 1; m11 >= 0; m11--) {
        const measurement = measurements[m11];
        if (furthestMeasurementsFound.has(measurement.lane)) {
          continue;
        }
        const previousFurthestMeasurement = furthestMeasurements.get(
          measurement.lane
        );
        if (previousFurthestMeasurement == null || measurement.end > previousFurthestMeasurement.end) {
          furthestMeasurements.set(measurement.lane, measurement);
        } else if (measurement.end < previousFurthestMeasurement.end) {
          furthestMeasurementsFound.set(measurement.lane, true);
        }
        if (furthestMeasurementsFound.size === this.options.lanes) {
          break;
        }
      }
      return furthestMeasurements.size === this.options.lanes ? Array.from(furthestMeasurements.values()).sort((a25, b11) => {
        if (a25.end === b11.end) {
          return a25.index - b11.index;
        }
        return a25.end - b11.end;
      })[0] : void 0;
    };
    this.getMeasurementOptions = memo(
      () => [
        this.options.count,
        this.options.paddingStart,
        this.options.scrollMargin,
        this.options.getItemKey,
        this.options.enabled
      ],
      (count2, paddingStart, scrollMargin, getItemKey, enabled) => {
        this.pendingMeasuredCacheIndexes = [];
        return {
          count: count2,
          paddingStart,
          scrollMargin,
          getItemKey,
          enabled
        };
      },
      {
        key: false
      }
    );
    this.getMeasurements = memo(
      () => [this.getMeasurementOptions(), this.itemSizeCache],
      ({ count: count2, paddingStart, scrollMargin, getItemKey, enabled }, itemSizeCache) => {
        if (!enabled) {
          this.measurementsCache = [];
          this.itemSizeCache.clear();
          return [];
        }
        if (this.measurementsCache.length === 0) {
          this.measurementsCache = this.options.initialMeasurementsCache;
          this.measurementsCache.forEach((item) => {
            this.itemSizeCache.set(item.key, item.size);
          });
        }
        const min2 = this.pendingMeasuredCacheIndexes.length > 0 ? Math.min(...this.pendingMeasuredCacheIndexes) : 0;
        this.pendingMeasuredCacheIndexes = [];
        const measurements = this.measurementsCache.slice(0, min2);
        for (let i19 = min2; i19 < count2; i19++) {
          const key = getItemKey(i19);
          const furthestMeasurement = this.options.lanes === 1 ? measurements[i19 - 1] : this.getFurthestMeasurement(measurements, i19);
          const start = furthestMeasurement ? furthestMeasurement.end + this.options.gap : paddingStart + scrollMargin;
          const measuredSize = itemSizeCache.get(key);
          const size4 = typeof measuredSize === "number" ? measuredSize : this.options.estimateSize(i19);
          const end = start + size4;
          const lane = furthestMeasurement ? furthestMeasurement.lane : i19 % this.options.lanes;
          measurements[i19] = {
            index: i19,
            start,
            size: size4,
            end,
            key,
            lane
          };
        }
        this.measurementsCache = measurements;
        return measurements;
      },
      {
        key: "getMeasurements",
        debug: () => this.options.debug
      }
    );
    this.calculateRange = memo(
      () => [
        this.getMeasurements(),
        this.getSize(),
        this.getScrollOffset(),
        this.options.lanes
      ],
      (measurements, outerSize, scrollOffset, lanes) => {
        return this.range = measurements.length > 0 && outerSize > 0 ? calculateRange({
          measurements,
          outerSize,
          scrollOffset,
          lanes
        }) : null;
      },
      {
        key: "calculateRange",
        debug: () => this.options.debug
      }
    );
    this.getVirtualIndexes = memo(
      () => {
        let startIndex = null;
        let endIndex = null;
        const range = this.calculateRange();
        if (range) {
          startIndex = range.startIndex;
          endIndex = range.endIndex;
        }
        this.maybeNotify.updateDeps([this.isScrolling, startIndex, endIndex]);
        return [
          this.options.rangeExtractor,
          this.options.overscan,
          this.options.count,
          startIndex,
          endIndex
        ];
      },
      (rangeExtractor, overscan, count2, startIndex, endIndex) => {
        return startIndex === null || endIndex === null ? [] : rangeExtractor({
          startIndex,
          endIndex,
          overscan,
          count: count2
        });
      },
      {
        key: "getVirtualIndexes",
        debug: () => this.options.debug
      }
    );
    this.indexFromElement = (node) => {
      const attributeName = this.options.indexAttribute;
      const indexStr = node.getAttribute(attributeName);
      if (!indexStr) {
        console.warn(
          `Missing attribute name '${attributeName}={index}' on measured element.`
        );
        return -1;
      }
      return parseInt(indexStr, 10);
    };
    this._measureElement = (node, entry) => {
      const index3 = this.indexFromElement(node);
      const item = this.measurementsCache[index3];
      if (!item) {
        return;
      }
      const key = item.key;
      const prevNode = this.elementsCache.get(key);
      if (prevNode !== node) {
        if (prevNode) {
          this.observer.unobserve(prevNode);
        }
        this.observer.observe(node);
        this.elementsCache.set(key, node);
      }
      if (node.isConnected) {
        this.resizeItem(index3, this.options.measureElement(node, entry, this));
      }
    };
    this.resizeItem = (index3, size4) => {
      const item = this.measurementsCache[index3];
      if (!item) {
        return;
      }
      const itemSize = this.itemSizeCache.get(item.key) ?? item.size;
      const delta = size4 - itemSize;
      if (delta !== 0) {
        if (this.shouldAdjustScrollPositionOnItemSizeChange !== void 0 ? this.shouldAdjustScrollPositionOnItemSizeChange(item, delta, this) : item.start < this.getScrollOffset() + this.scrollAdjustments) {
          if (this.options.debug) {
            console.info("correction", delta);
          }
          this._scrollToOffset(this.getScrollOffset(), {
            adjustments: this.scrollAdjustments += delta,
            behavior: void 0
          });
        }
        this.pendingMeasuredCacheIndexes.push(item.index);
        this.itemSizeCache = new Map(this.itemSizeCache.set(item.key, size4));
        this.notify(false);
      }
    };
    this.measureElement = (node) => {
      if (!node) {
        this.elementsCache.forEach((cached, key) => {
          if (!cached.isConnected) {
            this.observer.unobserve(cached);
            this.elementsCache.delete(key);
          }
        });
        return;
      }
      this._measureElement(node, void 0);
    };
    this.getVirtualItems = memo(
      () => [this.getVirtualIndexes(), this.getMeasurements()],
      (indexes, measurements) => {
        const virtualItems = [];
        for (let k5 = 0, len = indexes.length; k5 < len; k5++) {
          const i19 = indexes[k5];
          const measurement = measurements[i19];
          virtualItems.push(measurement);
        }
        return virtualItems;
      },
      {
        key: "getVirtualItems",
        debug: () => this.options.debug
      }
    );
    this.getVirtualItemForOffset = (offset4) => {
      const measurements = this.getMeasurements();
      if (measurements.length === 0) {
        return void 0;
      }
      return notUndefined(
        measurements[findNearestBinarySearch(
          0,
          measurements.length - 1,
          (index3) => notUndefined(measurements[index3]).start,
          offset4
        )]
      );
    };
    this.getOffsetForAlignment = (toOffset, align, itemSize = 0) => {
      const size4 = this.getSize();
      const scrollOffset = this.getScrollOffset();
      if (align === "auto") {
        align = toOffset >= scrollOffset + size4 ? "end" : "start";
      }
      if (align === "center") {
        toOffset += (itemSize - size4) / 2;
      } else if (align === "end") {
        toOffset -= size4;
      }
      const scrollSizeProp = this.options.horizontal ? "scrollWidth" : "scrollHeight";
      const scrollSize = this.scrollElement ? "document" in this.scrollElement ? this.scrollElement.document.documentElement[scrollSizeProp] : this.scrollElement[scrollSizeProp] : 0;
      const maxOffset = scrollSize - size4;
      return Math.max(Math.min(maxOffset, toOffset), 0);
    };
    this.getOffsetForIndex = (index3, align = "auto") => {
      index3 = Math.max(0, Math.min(index3, this.options.count - 1));
      const item = this.measurementsCache[index3];
      if (!item) {
        return void 0;
      }
      const size4 = this.getSize();
      const scrollOffset = this.getScrollOffset();
      if (align === "auto") {
        if (item.end >= scrollOffset + size4 - this.options.scrollPaddingEnd) {
          align = "end";
        } else if (item.start <= scrollOffset + this.options.scrollPaddingStart) {
          align = "start";
        } else {
          return [scrollOffset, align];
        }
      }
      const toOffset = align === "end" ? item.end + this.options.scrollPaddingEnd : item.start - this.options.scrollPaddingStart;
      return [
        this.getOffsetForAlignment(toOffset, align, item.size),
        align
      ];
    };
    this.isDynamicMode = () => this.elementsCache.size > 0;
    this.cancelScrollToIndex = () => {
      if (this.scrollToIndexTimeoutId !== null && this.targetWindow) {
        this.targetWindow.clearTimeout(this.scrollToIndexTimeoutId);
        this.scrollToIndexTimeoutId = null;
      }
    };
    this.scrollToOffset = (toOffset, { align = "start", behavior } = {}) => {
      this.cancelScrollToIndex();
      if (behavior === "smooth" && this.isDynamicMode()) {
        console.warn(
          "The `smooth` scroll behavior is not fully supported with dynamic size."
        );
      }
      this._scrollToOffset(this.getOffsetForAlignment(toOffset, align), {
        adjustments: void 0,
        behavior
      });
    };
    this.scrollToIndex = (index3, { align: initialAlign = "auto", behavior } = {}) => {
      index3 = Math.max(0, Math.min(index3, this.options.count - 1));
      this.cancelScrollToIndex();
      if (behavior === "smooth" && this.isDynamicMode()) {
        console.warn(
          "The `smooth` scroll behavior is not fully supported with dynamic size."
        );
      }
      const offsetAndAlign = this.getOffsetForIndex(index3, initialAlign);
      if (!offsetAndAlign) return;
      const [offset4, align] = offsetAndAlign;
      this._scrollToOffset(offset4, { adjustments: void 0, behavior });
      if (behavior !== "smooth" && this.isDynamicMode() && this.targetWindow) {
        this.scrollToIndexTimeoutId = this.targetWindow.setTimeout(() => {
          this.scrollToIndexTimeoutId = null;
          const elementInDOM = this.elementsCache.has(
            this.options.getItemKey(index3)
          );
          if (elementInDOM) {
            const [latestOffset] = notUndefined(
              this.getOffsetForIndex(index3, align)
            );
            if (!approxEqual(latestOffset, this.getScrollOffset())) {
              this.scrollToIndex(index3, { align, behavior });
            }
          } else {
            this.scrollToIndex(index3, { align, behavior });
          }
        });
      }
    };
    this.scrollBy = (delta, { behavior } = {}) => {
      this.cancelScrollToIndex();
      if (behavior === "smooth" && this.isDynamicMode()) {
        console.warn(
          "The `smooth` scroll behavior is not fully supported with dynamic size."
        );
      }
      this._scrollToOffset(this.getScrollOffset() + delta, {
        adjustments: void 0,
        behavior
      });
    };
    this.getTotalSize = () => {
      var _a;
      const measurements = this.getMeasurements();
      let end;
      if (measurements.length === 0) {
        end = this.options.paddingStart;
      } else if (this.options.lanes === 1) {
        end = ((_a = measurements[measurements.length - 1]) == null ? void 0 : _a.end) ?? 0;
      } else {
        const endByLane = Array(this.options.lanes).fill(null);
        let endIndex = measurements.length - 1;
        while (endIndex >= 0 && endByLane.some((val) => val === null)) {
          const item = measurements[endIndex];
          if (endByLane[item.lane] === null) {
            endByLane[item.lane] = item.end;
          }
          endIndex--;
        }
        end = Math.max(...endByLane.filter((val) => val !== null));
      }
      return Math.max(
        end - this.options.scrollMargin + this.options.paddingEnd,
        0
      );
    };
    this._scrollToOffset = (offset4, {
      adjustments,
      behavior
    }) => {
      this.options.scrollToFn(offset4, { behavior, adjustments }, this);
    };
    this.measure = () => {
      this.itemSizeCache = /* @__PURE__ */ new Map();
      this.notify(false);
    };
    this.setOptions(opts);
  }
};
var findNearestBinarySearch = (low, high, getCurrentValue, value) => {
  while (low <= high) {
    const middle = (low + high) / 2 | 0;
    const currentValue = getCurrentValue(middle);
    if (currentValue < value) {
      low = middle + 1;
    } else if (currentValue > value) {
      high = middle - 1;
    } else {
      return middle;
    }
  }
  if (low > 0) {
    return low - 1;
  } else {
    return 0;
  }
};
function calculateRange({
  measurements,
  outerSize,
  scrollOffset,
  lanes
}) {
  const lastIndex = measurements.length - 1;
  const getOffset = (index3) => measurements[index3].start;
  if (measurements.length <= lanes) {
    return {
      startIndex: 0,
      endIndex: lastIndex
    };
  }
  let startIndex = findNearestBinarySearch(
    0,
    lastIndex,
    getOffset,
    scrollOffset
  );
  let endIndex = startIndex;
  if (lanes === 1) {
    while (endIndex < lastIndex && measurements[endIndex].end < scrollOffset + outerSize) {
      endIndex++;
    }
  } else if (lanes > 1) {
    const endPerLane = Array(lanes).fill(0);
    while (endIndex < lastIndex && endPerLane.some((pos) => pos < scrollOffset + outerSize)) {
      const item = measurements[endIndex];
      endPerLane[item.lane] = item.end;
      endIndex++;
    }
    const startPerLane = Array(lanes).fill(scrollOffset + outerSize);
    while (startIndex >= 0 && startPerLane.some((pos) => pos >= scrollOffset)) {
      const item = measurements[startIndex];
      startPerLane[item.lane] = item.start;
      startIndex--;
    }
    startIndex = Math.max(0, startIndex - startIndex % lanes);
    endIndex = Math.min(lastIndex, endIndex + (lanes - 1 - endIndex % lanes));
  }
  return { startIndex, endIndex };
}

// node_modules/@tanstack/react-virtual/dist/esm/index.js
var useIsomorphicLayoutEffect = typeof document !== "undefined" ? React.useLayoutEffect : React.useEffect;
function useVirtualizerBase(options) {
  const rerender = React.useReducer(() => ({}), {})[1];
  const resolvedOptions = {
    ...options,
    onChange: (instance2, sync) => {
      var _a;
      if (sync) {
        (0, import_react_dom4.flushSync)(rerender);
      } else {
        rerender();
      }
      (_a = options.onChange) == null ? void 0 : _a.call(options, instance2, sync);
    }
  };
  const [instance] = React.useState(
    () => new Virtualizer(resolvedOptions)
  );
  instance.setOptions(resolvedOptions);
  useIsomorphicLayoutEffect(() => {
    return instance._didMount();
  }, []);
  useIsomorphicLayoutEffect(() => {
    return instance._willUpdate();
  });
  return instance;
}
function useVirtualizer(options) {
  return useVirtualizerBase({
    observeElementRect,
    observeElementOffset,
    scrollToFn: elementScroll,
    ...options
  });
}

// node_modules/@headlessui/react/dist/components/combobox/combobox.js
var import_react86 = __toESM(require_react(), 1);
var import_react_dom8 = __toESM(require_react_dom(), 1);

// node_modules/@headlessui/react/dist/hooks/use-by-comparator.js
var import_react59 = __toESM(require_react(), 1);
function l6(e8, r20) {
  return e8 !== null && r20 !== null && typeof e8 == "object" && typeof r20 == "object" && "id" in e8 && "id" in r20 ? e8.id === r20.id : e8 === r20;
}
function u8(e8 = l6) {
  return (0, import_react59.useCallback)((r20, t12) => {
    if (typeof e8 == "string") {
      let o21 = e8;
      return (r20 == null ? void 0 : r20[o21]) === (t12 == null ? void 0 : t12[o21]);
    }
    return e8(r20, t12);
  }, [e8]);
}

// node_modules/@headlessui/react/dist/hooks/use-element-size.js
var import_react60 = __toESM(require_react(), 1);
function f8(e8) {
  if (e8 === null) return { width: 0, height: 0 };
  let { width: t12, height: r20 } = e8.getBoundingClientRect();
  return { width: t12, height: r20 };
}
function d3(e8, t12 = false) {
  let [r20, u19] = (0, import_react60.useReducer)(() => ({}), {}), i19 = (0, import_react60.useMemo)(() => f8(e8), [e8, r20]);
  return n(() => {
    if (!e8) return;
    let n13 = new ResizeObserver(u19);
    return n13.observe(e8), () => {
      n13.disconnect();
    };
  }, [e8]), t12 ? { width: `${i19.width}px`, height: `${i19.height}px` } : i19;
}

// node_modules/@headlessui/react/dist/hooks/use-is-top-layer.js
var import_react62 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/default-map.js
var a6 = class extends Map {
  constructor(t12) {
    super();
    this.factory = t12;
  }
  get(t12) {
    let e8 = super.get(t12);
    return e8 === void 0 && (e8 = this.factory(t12), this.set(t12, e8)), e8;
  }
};

// node_modules/@headlessui/react/dist/utils/store.js
function a7(o21, r20) {
  let t12 = o21(), n13 = /* @__PURE__ */ new Set();
  return { getSnapshot() {
    return t12;
  }, subscribe(e8) {
    return n13.add(e8), () => n13.delete(e8);
  }, dispatch(e8, ...s15) {
    let i19 = r20[e8].call(t12, ...s15);
    i19 && (t12 = i19, n13.forEach((c19) => c19()));
  } };
}

// node_modules/@headlessui/react/dist/hooks/use-store.js
var import_react61 = __toESM(require_react(), 1);
function o11(t12) {
  return (0, import_react61.useSyncExternalStore)(t12.subscribe, t12.getSnapshot, t12.getSnapshot);
}

// node_modules/@headlessui/react/dist/hooks/use-is-top-layer.js
var p3 = new a6(() => a7(() => [], { ADD(r20) {
  return this.includes(r20) ? this : [...this, r20];
}, REMOVE(r20) {
  let e8 = this.indexOf(r20);
  if (e8 === -1) return this;
  let t12 = this.slice();
  return t12.splice(e8, 1), t12;
} }));
function x2(r20, e8) {
  let t12 = p3.get(e8), i19 = (0, import_react62.useId)(), h11 = o11(t12);
  if (n(() => {
    if (r20) return t12.dispatch("ADD", i19), () => t12.dispatch("REMOVE", i19);
  }, [t12, r20]), !r20) return false;
  let s15 = h11.indexOf(i19), o21 = h11.length;
  return s15 === -1 && (s15 = o21, o21 += 1), s15 === o21 - 1;
}

// node_modules/@headlessui/react/dist/hooks/use-inert-others.js
var f9 = /* @__PURE__ */ new Map();
var u9 = /* @__PURE__ */ new Map();
function h4(t12) {
  var e8;
  let r20 = (e8 = u9.get(t12)) != null ? e8 : 0;
  return u9.set(t12, r20 + 1), r20 !== 0 ? () => m5(t12) : (f9.set(t12, { "aria-hidden": t12.getAttribute("aria-hidden"), inert: t12.inert }), t12.setAttribute("aria-hidden", "true"), t12.inert = true, () => m5(t12));
}
function m5(t12) {
  var i19;
  let r20 = (i19 = u9.get(t12)) != null ? i19 : 1;
  if (r20 === 1 ? u9.delete(t12) : u9.set(t12, r20 - 1), r20 !== 1) return;
  let e8 = f9.get(t12);
  e8 && (e8["aria-hidden"] === null ? t12.removeAttribute("aria-hidden") : t12.setAttribute("aria-hidden", e8["aria-hidden"]), t12.inert = e8.inert, f9.delete(t12));
}
function y3(t12, { allowed: r20, disallowed: e8 } = {}) {
  let i19 = x2(t12, "inert-others");
  n(() => {
    var d14, c19;
    if (!i19) return;
    let a25 = o3();
    for (let n13 of (d14 = e8 == null ? void 0 : e8()) != null ? d14 : []) n13 && a25.add(h4(n13));
    let s15 = (c19 = r20 == null ? void 0 : r20()) != null ? c19 : [];
    for (let n13 of s15) {
      if (!n13) continue;
      let l17 = o2(n13);
      if (!l17) continue;
      let o21 = n13.parentElement;
      for (; o21 && o21 !== l17.body; ) {
        for (let p6 of o21.children) s15.some((E15) => p6.contains(E15)) || a25.add(h4(p6));
        o21 = o21.parentElement;
      }
    }
    return a25.dispose;
  }, [i19, r20, e8]);
}

// node_modules/@headlessui/react/dist/hooks/use-on-disappear.js
var import_react63 = __toESM(require_react(), 1);
function m6(s15, n13, l17) {
  let i19 = s3((t12) => {
    let e8 = t12.getBoundingClientRect();
    e8.x === 0 && e8.y === 0 && e8.width === 0 && e8.height === 0 && l17();
  });
  (0, import_react63.useEffect)(() => {
    if (!s15) return;
    let t12 = n13 === null ? null : n13 instanceof HTMLElement ? n13 : n13.current;
    if (!t12) return;
    let e8 = o3();
    if (typeof ResizeObserver != "undefined") {
      let r20 = new ResizeObserver(() => i19.current(t12));
      r20.observe(t12), e8.add(() => r20.disconnect());
    }
    if (typeof IntersectionObserver != "undefined") {
      let r20 = new IntersectionObserver(() => i19.current(t12));
      r20.observe(t12), e8.add(() => r20.disconnect());
    }
    return () => e8.dispose();
  }, [n13, i19, s15]);
}

// node_modules/@headlessui/react/dist/hooks/use-outside-click.js
var import_react66 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/focus-management.js
var f10 = ["[contentEditable=true]", "[tabindex]", "a[href]", "area[href]", "button:not([disabled])", "iframe", "input:not([disabled])", "select:not([disabled])", "textarea:not([disabled])"].map((e8) => `${e8}:not([tabindex='-1'])`).join(",");
var p4 = ["[data-autofocus]"].map((e8) => `${e8}:not([tabindex='-1'])`).join(",");
var F2 = ((n13) => (n13[n13.First = 1] = "First", n13[n13.Previous = 2] = "Previous", n13[n13.Next = 4] = "Next", n13[n13.Last = 8] = "Last", n13[n13.WrapAround = 16] = "WrapAround", n13[n13.NoScroll = 32] = "NoScroll", n13[n13.AutoFocus = 64] = "AutoFocus", n13))(F2 || {});
var T5 = ((o21) => (o21[o21.Error = 0] = "Error", o21[o21.Overflow = 1] = "Overflow", o21[o21.Success = 2] = "Success", o21[o21.Underflow = 3] = "Underflow", o21))(T5 || {});
var y4 = ((t12) => (t12[t12.Previous = -1] = "Previous", t12[t12.Next = 1] = "Next", t12))(y4 || {});
function b2(e8 = document.body) {
  return e8 == null ? [] : Array.from(e8.querySelectorAll(f10)).sort((r20, t12) => Math.sign((r20.tabIndex || Number.MAX_SAFE_INTEGER) - (t12.tabIndex || Number.MAX_SAFE_INTEGER)));
}
function S3(e8 = document.body) {
  return e8 == null ? [] : Array.from(e8.querySelectorAll(p4)).sort((r20, t12) => Math.sign((r20.tabIndex || Number.MAX_SAFE_INTEGER) - (t12.tabIndex || Number.MAX_SAFE_INTEGER)));
}
var h5 = ((t12) => (t12[t12.Strict = 0] = "Strict", t12[t12.Loose = 1] = "Loose", t12))(h5 || {});
function A2(e8, r20 = 0) {
  var t12;
  return e8 === ((t12 = o2(e8)) == null ? void 0 : t12.body) ? false : u(r20, { [0]() {
    return e8.matches(f10);
  }, [1]() {
    let u19 = e8;
    for (; u19 !== null; ) {
      if (u19.matches(f10)) return true;
      u19 = u19.parentElement;
    }
    return false;
  } });
}
function G2(e8) {
  let r20 = o2(e8);
  o3().nextFrame(() => {
    r20 && !A2(r20.activeElement, 0) && I2(e8);
  });
}
var H5 = ((t12) => (t12[t12.Keyboard = 0] = "Keyboard", t12[t12.Mouse = 1] = "Mouse", t12))(H5 || {});
typeof window != "undefined" && typeof document != "undefined" && (document.addEventListener("keydown", (e8) => {
  e8.metaKey || e8.altKey || e8.ctrlKey || (document.documentElement.dataset.headlessuiFocusVisible = "");
}, true), document.addEventListener("click", (e8) => {
  e8.detail === 1 ? delete document.documentElement.dataset.headlessuiFocusVisible : e8.detail === 0 && (document.documentElement.dataset.headlessuiFocusVisible = "");
}, true));
function I2(e8) {
  e8 == null || e8.focus({ preventScroll: true });
}
var w5 = ["textarea", "input"].join(",");
function O2(e8) {
  var r20, t12;
  return (t12 = (r20 = e8 == null ? void 0 : e8.matches) == null ? void 0 : r20.call(e8, w5)) != null ? t12 : false;
}
function _3(e8, r20 = (t12) => t12) {
  return e8.slice().sort((t12, u19) => {
    let o21 = r20(t12), c19 = r20(u19);
    if (o21 === null || c19 === null) return 0;
    let l17 = o21.compareDocumentPosition(c19);
    return l17 & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : l17 & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
  });
}
function j3(e8, r20) {
  return P6(b2(), r20, { relativeTo: e8 });
}
function P6(e8, r20, { sorted: t12 = true, relativeTo: u19 = null, skipElements: o21 = [] } = {}) {
  let c19 = Array.isArray(e8) ? e8.length > 0 ? e8[0].ownerDocument : document : e8.ownerDocument, l17 = Array.isArray(e8) ? t12 ? _3(e8) : e8 : r20 & 64 ? S3(e8) : b2(e8);
  o21.length > 0 && l17.length > 1 && (l17 = l17.filter((s15) => !o21.some((a25) => a25 != null && "current" in a25 ? (a25 == null ? void 0 : a25.current) === s15 : a25 === s15))), u19 = u19 != null ? u19 : c19.activeElement;
  let n13 = (() => {
    if (r20 & 5) return 1;
    if (r20 & 10) return -1;
    throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last");
  })(), x11 = (() => {
    if (r20 & 1) return 0;
    if (r20 & 2) return Math.max(0, l17.indexOf(u19)) - 1;
    if (r20 & 4) return Math.max(0, l17.indexOf(u19)) + 1;
    if (r20 & 8) return l17.length - 1;
    throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last");
  })(), M11 = r20 & 32 ? { preventScroll: true } : {}, m11 = 0, d14 = l17.length, i19;
  do {
    if (m11 >= d14 || m11 + d14 <= 0) return 0;
    let s15 = x11 + m11;
    if (r20 & 16) s15 = (s15 + d14) % d14;
    else {
      if (s15 < 0) return 3;
      if (s15 >= d14) return 1;
    }
    i19 = l17[s15], i19 == null || i19.focus(M11), m11 += n13;
  } while (i19 !== c19.activeElement);
  return r20 & 6 && O2(i19) && i19.select(), 2;
}

// node_modules/@headlessui/react/dist/utils/platform.js
function t4() {
  return /iPhone/gi.test(window.navigator.platform) || /Mac/gi.test(window.navigator.platform) && window.navigator.maxTouchPoints > 0;
}
function i8() {
  return /Android/gi.test(window.navigator.userAgent);
}
function n8() {
  return t4() || i8();
}

// node_modules/@headlessui/react/dist/hooks/use-document-event.js
var import_react64 = __toESM(require_react(), 1);
function i9(t12, e8, o21, n13) {
  let u19 = s3(o21);
  (0, import_react64.useEffect)(() => {
    if (!t12) return;
    function r20(m11) {
      u19.current(m11);
    }
    return document.addEventListener(e8, r20, n13), () => document.removeEventListener(e8, r20, n13);
  }, [t12, e8, n13]);
}

// node_modules/@headlessui/react/dist/hooks/use-window-event.js
var import_react65 = __toESM(require_react(), 1);
function s5(t12, e8, o21, n13) {
  let i19 = s3(o21);
  (0, import_react65.useEffect)(() => {
    if (!t12) return;
    function r20(d14) {
      i19.current(d14);
    }
    return window.addEventListener(e8, r20, n13), () => window.removeEventListener(e8, r20, n13);
  }, [t12, e8, n13]);
}

// node_modules/@headlessui/react/dist/hooks/use-outside-click.js
var E4 = 30;
function R3(p6, f22, C10) {
  let u19 = x2(p6, "outside-click"), m11 = s3(C10), s15 = (0, import_react66.useCallback)(function(e8, n13) {
    if (e8.defaultPrevented) return;
    let r20 = n13(e8);
    if (r20 === null || !r20.getRootNode().contains(r20) || !r20.isConnected) return;
    let h11 = function l17(o21) {
      return typeof o21 == "function" ? l17(o21()) : Array.isArray(o21) || o21 instanceof Set ? o21 : [o21];
    }(f22);
    for (let l17 of h11) if (l17 !== null && (l17.contains(r20) || e8.composed && e8.composedPath().includes(l17))) return;
    return !A2(r20, h5.Loose) && r20.tabIndex !== -1 && e8.preventDefault(), m11.current(e8, r20);
  }, [m11, f22]), i19 = (0, import_react66.useRef)(null);
  i9(u19, "pointerdown", (t12) => {
    var e8, n13;
    i19.current = ((n13 = (e8 = t12.composedPath) == null ? void 0 : e8.call(t12)) == null ? void 0 : n13[0]) || t12.target;
  }, true), i9(u19, "mousedown", (t12) => {
    var e8, n13;
    i19.current = ((n13 = (e8 = t12.composedPath) == null ? void 0 : e8.call(t12)) == null ? void 0 : n13[0]) || t12.target;
  }, true), i9(u19, "click", (t12) => {
    n8() || i19.current && (s15(t12, () => i19.current), i19.current = null);
  }, true);
  let a25 = (0, import_react66.useRef)({ x: 0, y: 0 });
  i9(u19, "touchstart", (t12) => {
    a25.current.x = t12.touches[0].clientX, a25.current.y = t12.touches[0].clientY;
  }, true), i9(u19, "touchend", (t12) => {
    let e8 = { x: t12.changedTouches[0].clientX, y: t12.changedTouches[0].clientY };
    if (!(Math.abs(e8.x - a25.current.x) >= E4 || Math.abs(e8.y - a25.current.y) >= E4)) return s15(t12, () => t12.target instanceof HTMLElement ? t12.target : null);
  }, true), s5(u19, "blur", (t12) => s15(t12, () => window.document.activeElement instanceof HTMLIFrameElement ? window.document.activeElement : null), true);
}

// node_modules/@headlessui/react/dist/hooks/use-owner.js
var import_react67 = __toESM(require_react(), 1);
function n9(...e8) {
  return (0, import_react67.useMemo)(() => o2(...e8), [...e8]);
}

// node_modules/@headlessui/react/dist/hooks/use-refocusable-input.js
var import_react69 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-event-listener.js
var import_react68 = __toESM(require_react(), 1);
function E5(n13, e8, a25, t12) {
  let i19 = s3(a25);
  (0, import_react68.useEffect)(() => {
    n13 = n13 != null ? n13 : window;
    function r20(o21) {
      i19.current(o21);
    }
    return n13.addEventListener(e8, r20, t12), () => n13.removeEventListener(e8, r20, t12);
  }, [n13, e8, t12]);
}

// node_modules/@headlessui/react/dist/hooks/use-refocusable-input.js
function i10(e8) {
  let n13 = (0, import_react69.useRef)({ value: "", selectionStart: null, selectionEnd: null });
  return E5(e8, "blur", (l17) => {
    let t12 = l17.target;
    t12 instanceof HTMLInputElement && (n13.current = { value: t12.value, selectionStart: t12.selectionStart, selectionEnd: t12.selectionEnd });
  }), o5(() => {
    if (document.activeElement !== e8 && e8 instanceof HTMLInputElement && e8.isConnected) {
      if (e8.focus({ preventScroll: true }), e8.value !== n13.current.value) e8.setSelectionRange(e8.value.length, e8.value.length);
      else {
        let { selectionStart: l17, selectionEnd: t12 } = n13.current;
        l17 !== null && t12 !== null && e8.setSelectionRange(l17, t12);
      }
      n13.current = { value: "", selectionStart: null, selectionEnd: null };
    }
  });
}

// node_modules/@headlessui/react/dist/hooks/use-resolve-button-type.js
var import_react70 = __toESM(require_react(), 1);
function e6(t12, u19) {
  return (0, import_react70.useMemo)(() => {
    var n13;
    if (t12.type) return t12.type;
    let r20 = (n13 = t12.as) != null ? n13 : "button";
    if (typeof r20 == "string" && r20.toLowerCase() === "button" || (u19 == null ? void 0 : u19.tagName) === "BUTTON" && !u19.hasAttribute("type")) return "button";
  }, [t12.type, t12.as, u19]);
}

// node_modules/@headlessui/react/dist/hooks/document-overflow/adjust-scrollbar-padding.js
function d6() {
  let r20;
  return { before({ doc: e8 }) {
    var l17;
    let o21 = e8.documentElement, t12 = (l17 = e8.defaultView) != null ? l17 : window;
    r20 = Math.max(0, t12.innerWidth - o21.clientWidth);
  }, after({ doc: e8, d: o21 }) {
    let t12 = e8.documentElement, l17 = Math.max(0, t12.clientWidth - t12.offsetWidth), n13 = Math.max(0, r20 - l17);
    o21.style(t12, "paddingRight", `${n13}px`);
  } };
}

// node_modules/@headlessui/react/dist/hooks/document-overflow/handle-ios-locking.js
function d7() {
  return t4() ? { before({ doc: r20, d: n13, meta: c19 }) {
    function o21(a25) {
      return c19.containers.flatMap((l17) => l17()).some((l17) => l17.contains(a25));
    }
    n13.microTask(() => {
      var s15;
      if (window.getComputedStyle(r20.documentElement).scrollBehavior !== "auto") {
        let t12 = o3();
        t12.style(r20.documentElement, "scrollBehavior", "auto"), n13.add(() => n13.microTask(() => t12.dispose()));
      }
      let a25 = (s15 = window.scrollY) != null ? s15 : window.pageYOffset, l17 = null;
      n13.addEventListener(r20, "click", (t12) => {
        if (t12.target instanceof HTMLElement) try {
          let e8 = t12.target.closest("a");
          if (!e8) return;
          let { hash: f22 } = new URL(e8.href), i19 = r20.querySelector(f22);
          i19 && !o21(i19) && (l17 = i19);
        } catch {
        }
      }, true), n13.addEventListener(r20, "touchstart", (t12) => {
        if (t12.target instanceof HTMLElement) if (o21(t12.target)) {
          let e8 = t12.target;
          for (; e8.parentElement && o21(e8.parentElement); ) e8 = e8.parentElement;
          n13.style(e8, "overscrollBehavior", "contain");
        } else n13.style(t12.target, "touchAction", "none");
      }), n13.addEventListener(r20, "touchmove", (t12) => {
        if (t12.target instanceof HTMLElement) {
          if (t12.target.tagName === "INPUT") return;
          if (o21(t12.target)) {
            let e8 = t12.target;
            for (; e8.parentElement && e8.dataset.headlessuiPortal !== "" && !(e8.scrollHeight > e8.clientHeight || e8.scrollWidth > e8.clientWidth); ) e8 = e8.parentElement;
            e8.dataset.headlessuiPortal === "" && t12.preventDefault();
          } else t12.preventDefault();
        }
      }, { passive: false }), n13.add(() => {
        var e8;
        let t12 = (e8 = window.scrollY) != null ? e8 : window.pageYOffset;
        a25 !== t12 && window.scrollTo(0, a25), l17 && l17.isConnected && (l17.scrollIntoView({ block: "nearest" }), l17 = null);
      });
    });
  } } : {};
}

// node_modules/@headlessui/react/dist/hooks/document-overflow/prevent-scroll.js
function r7() {
  return { before({ doc: e8, d: o21 }) {
    o21.style(e8.documentElement, "overflow", "hidden");
  } };
}

// node_modules/@headlessui/react/dist/hooks/document-overflow/overflow-store.js
function m7(e8) {
  let n13 = {};
  for (let t12 of e8) Object.assign(n13, t12(n13));
  return n13;
}
var a10 = a7(() => /* @__PURE__ */ new Map(), { PUSH(e8, n13) {
  var o21;
  let t12 = (o21 = this.get(e8)) != null ? o21 : { doc: e8, count: 0, d: o3(), meta: /* @__PURE__ */ new Set() };
  return t12.count++, t12.meta.add(n13), this.set(e8, t12), this;
}, POP(e8, n13) {
  let t12 = this.get(e8);
  return t12 && (t12.count--, t12.meta.delete(n13)), this;
}, SCROLL_PREVENT({ doc: e8, d: n13, meta: t12 }) {
  let o21 = { doc: e8, d: n13, meta: m7(t12) }, c19 = [d7(), d6(), r7()];
  c19.forEach(({ before: r20 }) => r20 == null ? void 0 : r20(o21)), c19.forEach(({ after: r20 }) => r20 == null ? void 0 : r20(o21));
}, SCROLL_ALLOW({ d: e8 }) {
  e8.dispose();
}, TEARDOWN({ doc: e8 }) {
  this.delete(e8);
} });
a10.subscribe(() => {
  let e8 = a10.getSnapshot(), n13 = /* @__PURE__ */ new Map();
  for (let [t12] of e8) n13.set(t12, t12.documentElement.style.overflow);
  for (let t12 of e8.values()) {
    let o21 = n13.get(t12.doc) === "hidden", c19 = t12.count !== 0;
    (c19 && !o21 || !c19 && o21) && a10.dispatch(t12.count > 0 ? "SCROLL_PREVENT" : "SCROLL_ALLOW", t12), t12.count === 0 && a10.dispatch("TEARDOWN", t12);
  }
});

// node_modules/@headlessui/react/dist/hooks/document-overflow/use-document-overflow.js
function a11(r20, e8, n13 = () => ({ containers: [] })) {
  let f22 = o11(a10), o21 = e8 ? f22.get(e8) : void 0, i19 = o21 ? o21.count > 0 : false;
  return n(() => {
    if (!(!e8 || !r20)) return a10.dispatch("PUSH", e8, n13), () => a10.dispatch("POP", e8, n13);
  }, [r20, e8]), i19;
}

// node_modules/@headlessui/react/dist/hooks/use-scroll-lock.js
function f11(e8, c19, n13 = () => [document.body]) {
  let r20 = x2(e8, "scroll-lock");
  a11(r20, c19, (t12) => {
    var o21;
    return { containers: [...(o21 = t12.containers) != null ? o21 : [], n13] };
  });
}

// node_modules/@headlessui/react/dist/hooks/use-tracked-pointer.js
var import_react71 = __toESM(require_react(), 1);
function t6(e8) {
  return [e8.screenX, e8.screenY];
}
function u10() {
  let e8 = (0, import_react71.useRef)([-1, -1]);
  return { wasMoved(r20) {
    let n13 = t6(r20);
    return e8.current[0] === n13[0] && e8.current[1] === n13[1] ? false : (e8.current = n13, true);
  }, update(r20) {
    e8.current = t6(r20);
  } };
}

// node_modules/@headlessui/react/dist/hooks/use-transition.js
var import_react73 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-flags.js
var import_react72 = __toESM(require_react(), 1);
function c6(u19 = 0) {
  let [t12, l17] = (0, import_react72.useState)(u19), g8 = (0, import_react72.useCallback)((e8) => l17(e8), [t12]), s15 = (0, import_react72.useCallback)((e8) => l17((a25) => a25 | e8), [t12]), m11 = (0, import_react72.useCallback)((e8) => (t12 & e8) === e8, [t12]), n13 = (0, import_react72.useCallback)((e8) => l17((a25) => a25 & ~e8), [l17]), F9 = (0, import_react72.useCallback)((e8) => l17((a25) => a25 ^ e8), [l17]);
  return { flags: t12, setFlag: g8, addFlag: s15, hasFlag: m11, removeFlag: n13, toggleFlag: F9 };
}

// node_modules/@headlessui/react/dist/hooks/use-transition.js
var T7;
var b4;
typeof process != "undefined" && typeof globalThis != "undefined" && typeof Element != "undefined" && ((T7 = process == null ? void 0 : process.env) == null ? void 0 : T7["NODE_ENV"]) === "test" && typeof ((b4 = Element == null ? void 0 : Element.prototype) == null ? void 0 : b4.getAnimations) == "undefined" && (Element.prototype.getAnimations = function() {
  return console.warn(["Headless UI has polyfilled `Element.prototype.getAnimations` for your tests.", "Please install a proper polyfill e.g. `jsdom-testing-mocks`, to silence these warnings.", "", "Example usage:", "```js", "import { mockAnimationsApi } from 'jsdom-testing-mocks'", "mockAnimationsApi()", "```"].join(`
`)), [];
});
var L2 = ((r20) => (r20[r20.None = 0] = "None", r20[r20.Closed = 1] = "Closed", r20[r20.Enter = 2] = "Enter", r20[r20.Leave = 4] = "Leave", r20))(L2 || {});
function R4(t12) {
  let n13 = {};
  for (let e8 in t12) t12[e8] === true && (n13[`data-${e8}`] = "");
  return n13;
}
function x3(t12, n13, e8, i19) {
  let [r20, o21] = (0, import_react73.useState)(e8), { hasFlag: s15, addFlag: a25, removeFlag: l17 } = c6(t12 && r20 ? 3 : 0), u19 = (0, import_react73.useRef)(false), f22 = (0, import_react73.useRef)(false), E15 = p();
  return n(() => {
    var d14;
    if (t12) {
      if (e8 && o21(true), !n13) {
        e8 && a25(3);
        return;
      }
      return (d14 = i19 == null ? void 0 : i19.start) == null || d14.call(i19, e8), C5(n13, { inFlight: u19, prepare() {
        f22.current ? f22.current = false : f22.current = u19.current, u19.current = true, !f22.current && (e8 ? (a25(3), l17(4)) : (a25(4), l17(2)));
      }, run() {
        f22.current ? e8 ? (l17(3), a25(4)) : (l17(4), a25(3)) : e8 ? l17(1) : a25(1);
      }, done() {
        var p6;
        f22.current && typeof n13.getAnimations == "function" && n13.getAnimations().length > 0 || (u19.current = false, l17(7), e8 || o21(false), (p6 = i19 == null ? void 0 : i19.end) == null || p6.call(i19, e8));
      } });
    }
  }, [t12, e8, n13, E15]), t12 ? [r20, { closed: s15(1), enter: s15(2), leave: s15(4), transition: s15(2) || s15(4) }] : [e8, { closed: void 0, enter: void 0, leave: void 0, transition: void 0 }];
}
function C5(t12, { prepare: n13, run: e8, done: i19, inFlight: r20 }) {
  let o21 = o3();
  return j4(t12, { prepare: n13, inFlight: r20 }), o21.nextFrame(() => {
    e8(), o21.requestAnimationFrame(() => {
      o21.add(M(t12, i19));
    });
  }), o21.dispose;
}
function M(t12, n13) {
  var o21, s15;
  let e8 = o3();
  if (!t12) return e8.dispose;
  let i19 = false;
  e8.add(() => {
    i19 = true;
  });
  let r20 = (s15 = (o21 = t12.getAnimations) == null ? void 0 : o21.call(t12).filter((a25) => a25 instanceof CSSTransition)) != null ? s15 : [];
  return r20.length === 0 ? (n13(), e8.dispose) : (Promise.allSettled(r20.map((a25) => a25.finished)).then(() => {
    i19 || n13();
  }), e8.dispose);
}
function j4(t12, { inFlight: n13, prepare: e8 }) {
  if (n13 != null && n13.current) {
    e8();
    return;
  }
  let i19 = t12.style.transition;
  t12.style.transition = "none", e8(), t12.offsetHeight, t12.style.transition = i19;
}

// node_modules/@headlessui/react/dist/hooks/use-tree-walker.js
var import_react74 = __toESM(require_react(), 1);
function F3(c19, { container: e8, accept: t12, walk: r20 }) {
  let o21 = (0, import_react74.useRef)(t12), l17 = (0, import_react74.useRef)(r20);
  (0, import_react74.useEffect)(() => {
    o21.current = t12, l17.current = r20;
  }, [t12, r20]), n(() => {
    if (!e8 || !c19) return;
    let n13 = o2(e8);
    if (!n13) return;
    let f22 = o21.current, p6 = l17.current, i19 = Object.assign((m11) => f22(m11), { acceptNode: f22 }), u19 = n13.createTreeWalker(e8, NodeFilter.SHOW_ELEMENT, i19, false);
    for (; u19.nextNode(); ) p6(u19.currentNode);
  }, [e8, c19, o21, l17]);
}

// node_modules/@headlessui/react/dist/hooks/use-watch.js
var import_react75 = __toESM(require_react(), 1);
function m8(u19, t12) {
  let e8 = (0, import_react75.useRef)([]), r20 = o5(u19);
  (0, import_react75.useEffect)(() => {
    let o21 = [...e8.current];
    for (let [a25, l17] of t12.entries()) if (e8.current[a25] !== l17) {
      let n13 = r20(t12, o21);
      return e8.current = t12, n13;
    }
  }, [r20, ...t12]);
}

// node_modules/@floating-ui/react/dist/floating-ui.react.mjs
var React3 = __toESM(require_react(), 1);
var import_react77 = __toESM(require_react(), 1);

// node_modules/@floating-ui/utils/dist/floating-ui.utils.dom.mjs
function hasWindow() {
  return typeof window !== "undefined";
}
function getNodeName(node) {
  if (isNode(node)) {
    return (node.nodeName || "").toLowerCase();
  }
  return "#document";
}
function getWindow(node) {
  var _node$ownerDocument;
  return (node == null || (_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.defaultView) || window;
}
function getDocumentElement(node) {
  var _ref;
  return (_ref = (isNode(node) ? node.ownerDocument : node.document) || window.document) == null ? void 0 : _ref.documentElement;
}
function isNode(value) {
  if (!hasWindow()) {
    return false;
  }
  return value instanceof Node || value instanceof getWindow(value).Node;
}
function isElement(value) {
  if (!hasWindow()) {
    return false;
  }
  return value instanceof Element || value instanceof getWindow(value).Element;
}
function isHTMLElement(value) {
  if (!hasWindow()) {
    return false;
  }
  return value instanceof HTMLElement || value instanceof getWindow(value).HTMLElement;
}
function isShadowRoot(value) {
  if (!hasWindow() || typeof ShadowRoot === "undefined") {
    return false;
  }
  return value instanceof ShadowRoot || value instanceof getWindow(value).ShadowRoot;
}
function isOverflowElement(element) {
  const {
    overflow,
    overflowX,
    overflowY,
    display
  } = getComputedStyle2(element);
  return /auto|scroll|overlay|hidden|clip/.test(overflow + overflowY + overflowX) && !["inline", "contents"].includes(display);
}
function isTableElement(element) {
  return ["table", "td", "th"].includes(getNodeName(element));
}
function isTopLayer(element) {
  return [":popover-open", ":modal"].some((selector) => {
    try {
      return element.matches(selector);
    } catch (e8) {
      return false;
    }
  });
}
function isContainingBlock(elementOrCss) {
  const webkit = isWebKit();
  const css = isElement(elementOrCss) ? getComputedStyle2(elementOrCss) : elementOrCss;
  return ["transform", "translate", "scale", "rotate", "perspective"].some((value) => css[value] ? css[value] !== "none" : false) || (css.containerType ? css.containerType !== "normal" : false) || !webkit && (css.backdropFilter ? css.backdropFilter !== "none" : false) || !webkit && (css.filter ? css.filter !== "none" : false) || ["transform", "translate", "scale", "rotate", "perspective", "filter"].some((value) => (css.willChange || "").includes(value)) || ["paint", "layout", "strict", "content"].some((value) => (css.contain || "").includes(value));
}
function getContainingBlock(element) {
  let currentNode = getParentNode(element);
  while (isHTMLElement(currentNode) && !isLastTraversableNode(currentNode)) {
    if (isContainingBlock(currentNode)) {
      return currentNode;
    } else if (isTopLayer(currentNode)) {
      return null;
    }
    currentNode = getParentNode(currentNode);
  }
  return null;
}
function isWebKit() {
  if (typeof CSS === "undefined" || !CSS.supports) return false;
  return CSS.supports("-webkit-backdrop-filter", "none");
}
function isLastTraversableNode(node) {
  return ["html", "body", "#document"].includes(getNodeName(node));
}
function getComputedStyle2(element) {
  return getWindow(element).getComputedStyle(element);
}
function getNodeScroll(element) {
  if (isElement(element)) {
    return {
      scrollLeft: element.scrollLeft,
      scrollTop: element.scrollTop
    };
  }
  return {
    scrollLeft: element.scrollX,
    scrollTop: element.scrollY
  };
}
function getParentNode(node) {
  if (getNodeName(node) === "html") {
    return node;
  }
  const result = (
    // Step into the shadow DOM of the parent of a slotted node.
    node.assignedSlot || // DOM Element detected.
    node.parentNode || // ShadowRoot detected.
    isShadowRoot(node) && node.host || // Fallback.
    getDocumentElement(node)
  );
  return isShadowRoot(result) ? result.host : result;
}
function getNearestOverflowAncestor(node) {
  const parentNode = getParentNode(node);
  if (isLastTraversableNode(parentNode)) {
    return node.ownerDocument ? node.ownerDocument.body : node.body;
  }
  if (isHTMLElement(parentNode) && isOverflowElement(parentNode)) {
    return parentNode;
  }
  return getNearestOverflowAncestor(parentNode);
}
function getOverflowAncestors(node, list, traverseIframes) {
  var _node$ownerDocument2;
  if (list === void 0) {
    list = [];
  }
  if (traverseIframes === void 0) {
    traverseIframes = true;
  }
  const scrollableAncestor = getNearestOverflowAncestor(node);
  const isBody = scrollableAncestor === ((_node$ownerDocument2 = node.ownerDocument) == null ? void 0 : _node$ownerDocument2.body);
  const win = getWindow(scrollableAncestor);
  if (isBody) {
    const frameElement = getFrameElement(win);
    return list.concat(win, win.visualViewport || [], isOverflowElement(scrollableAncestor) ? scrollableAncestor : [], frameElement && traverseIframes ? getOverflowAncestors(frameElement) : []);
  }
  return list.concat(scrollableAncestor, getOverflowAncestors(scrollableAncestor, [], traverseIframes));
}
function getFrameElement(win) {
  return win.parent && Object.getPrototypeOf(win.parent) ? win.frameElement : null;
}

// node_modules/@floating-ui/react/dist/floating-ui.react.utils.mjs
function getPlatform() {
  const uaData = navigator.userAgentData;
  if (uaData != null && uaData.platform) {
    return uaData.platform;
  }
  return navigator.platform;
}
function getUserAgent() {
  const uaData = navigator.userAgentData;
  if (uaData && Array.isArray(uaData.brands)) {
    return uaData.brands.map((_ref) => {
      let {
        brand,
        version
      } = _ref;
      return brand + "/" + version;
    }).join(" ");
  }
  return navigator.userAgent;
}
function isSafari() {
  return /apple/i.test(navigator.vendor);
}
function stopEvent(event) {
  event.preventDefault();
  event.stopPropagation();
}

// node_modules/@floating-ui/utils/dist/floating-ui.utils.mjs
var sides = ["top", "right", "bottom", "left"];
var alignments = ["start", "end"];
var placements = sides.reduce((acc, side) => acc.concat(side, side + "-" + alignments[0], side + "-" + alignments[1]), []);
var min = Math.min;
var max = Math.max;
var round = Math.round;
var floor = Math.floor;
var createCoords = (v5) => ({
  x: v5,
  y: v5
});
var oppositeSideMap = {
  left: "right",
  right: "left",
  bottom: "top",
  top: "bottom"
};
var oppositeAlignmentMap = {
  start: "end",
  end: "start"
};
function clamp(start, value, end) {
  return max(start, min(value, end));
}
function evaluate(value, param) {
  return typeof value === "function" ? value(param) : value;
}
function getSide(placement) {
  return placement.split("-")[0];
}
function getAlignment(placement) {
  return placement.split("-")[1];
}
function getOppositeAxis(axis) {
  return axis === "x" ? "y" : "x";
}
function getAxisLength(axis) {
  return axis === "y" ? "height" : "width";
}
function getSideAxis(placement) {
  return ["top", "bottom"].includes(getSide(placement)) ? "y" : "x";
}
function getAlignmentAxis(placement) {
  return getOppositeAxis(getSideAxis(placement));
}
function getAlignmentSides(placement, rects, rtl) {
  if (rtl === void 0) {
    rtl = false;
  }
  const alignment = getAlignment(placement);
  const alignmentAxis = getAlignmentAxis(placement);
  const length = getAxisLength(alignmentAxis);
  let mainAlignmentSide = alignmentAxis === "x" ? alignment === (rtl ? "end" : "start") ? "right" : "left" : alignment === "start" ? "bottom" : "top";
  if (rects.reference[length] > rects.floating[length]) {
    mainAlignmentSide = getOppositePlacement(mainAlignmentSide);
  }
  return [mainAlignmentSide, getOppositePlacement(mainAlignmentSide)];
}
function getExpandedPlacements(placement) {
  const oppositePlacement = getOppositePlacement(placement);
  return [getOppositeAlignmentPlacement(placement), oppositePlacement, getOppositeAlignmentPlacement(oppositePlacement)];
}
function getOppositeAlignmentPlacement(placement) {
  return placement.replace(/start|end/g, (alignment) => oppositeAlignmentMap[alignment]);
}
function getSideList(side, isStart, rtl) {
  const lr = ["left", "right"];
  const rl = ["right", "left"];
  const tb = ["top", "bottom"];
  const bt2 = ["bottom", "top"];
  switch (side) {
    case "top":
    case "bottom":
      if (rtl) return isStart ? rl : lr;
      return isStart ? lr : rl;
    case "left":
    case "right":
      return isStart ? tb : bt2;
    default:
      return [];
  }
}
function getOppositeAxisPlacements(placement, flipAlignment, direction, rtl) {
  const alignment = getAlignment(placement);
  let list = getSideList(getSide(placement), direction === "start", rtl);
  if (alignment) {
    list = list.map((side) => side + "-" + alignment);
    if (flipAlignment) {
      list = list.concat(list.map(getOppositeAlignmentPlacement));
    }
  }
  return list;
}
function getOppositePlacement(placement) {
  return placement.replace(/left|right|bottom|top/g, (side) => oppositeSideMap[side]);
}
function expandPaddingObject(padding) {
  return {
    top: 0,
    right: 0,
    bottom: 0,
    left: 0,
    ...padding
  };
}
function getPaddingObject(padding) {
  return typeof padding !== "number" ? expandPaddingObject(padding) : {
    top: padding,
    right: padding,
    bottom: padding,
    left: padding
  };
}
function rectToClientRect(rect) {
  const {
    x: x11,
    y: y8,
    width,
    height
  } = rect;
  return {
    width,
    height,
    top: y8,
    left: x11,
    right: x11 + width,
    bottom: y8 + height,
    x: x11,
    y: y8
  };
}

// node_modules/tabbable/dist/index.esm.js
var candidateSelectors = ["input:not([inert])", "select:not([inert])", "textarea:not([inert])", "a[href]:not([inert])", "button:not([inert])", "[tabindex]:not(slot):not([inert])", "audio[controls]:not([inert])", "video[controls]:not([inert])", '[contenteditable]:not([contenteditable="false"]):not([inert])', "details>summary:first-of-type:not([inert])", "details:not([inert])"];
var candidateSelector = candidateSelectors.join(",");
var NoElement = typeof Element === "undefined";
var matches = NoElement ? function() {
} : Element.prototype.matches || Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
var getRootNode = !NoElement && Element.prototype.getRootNode ? function(element) {
  var _element$getRootNode;
  return element === null || element === void 0 ? void 0 : (_element$getRootNode = element.getRootNode) === null || _element$getRootNode === void 0 ? void 0 : _element$getRootNode.call(element);
} : function(element) {
  return element === null || element === void 0 ? void 0 : element.ownerDocument;
};
var focusableCandidateSelector = candidateSelectors.concat("iframe").join(",");

// node_modules/@floating-ui/react/dist/floating-ui.react.mjs
var ReactDOM2 = __toESM(require_react_dom(), 1);

// node_modules/@floating-ui/core/dist/floating-ui.core.mjs
function computeCoordsFromPlacement(_ref, placement, rtl) {
  let {
    reference,
    floating
  } = _ref;
  const sideAxis = getSideAxis(placement);
  const alignmentAxis = getAlignmentAxis(placement);
  const alignLength = getAxisLength(alignmentAxis);
  const side = getSide(placement);
  const isVertical = sideAxis === "y";
  const commonX = reference.x + reference.width / 2 - floating.width / 2;
  const commonY = reference.y + reference.height / 2 - floating.height / 2;
  const commonAlign = reference[alignLength] / 2 - floating[alignLength] / 2;
  let coords;
  switch (side) {
    case "top":
      coords = {
        x: commonX,
        y: reference.y - floating.height
      };
      break;
    case "bottom":
      coords = {
        x: commonX,
        y: reference.y + reference.height
      };
      break;
    case "right":
      coords = {
        x: reference.x + reference.width,
        y: commonY
      };
      break;
    case "left":
      coords = {
        x: reference.x - floating.width,
        y: commonY
      };
      break;
    default:
      coords = {
        x: reference.x,
        y: reference.y
      };
  }
  switch (getAlignment(placement)) {
    case "start":
      coords[alignmentAxis] -= commonAlign * (rtl && isVertical ? -1 : 1);
      break;
    case "end":
      coords[alignmentAxis] += commonAlign * (rtl && isVertical ? -1 : 1);
      break;
  }
  return coords;
}
var computePosition = async (reference, floating, config) => {
  const {
    placement = "bottom",
    strategy = "absolute",
    middleware = [],
    platform: platform2
  } = config;
  const validMiddleware = middleware.filter(Boolean);
  const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(floating));
  let rects = await platform2.getElementRects({
    reference,
    floating,
    strategy
  });
  let {
    x: x11,
    y: y8
  } = computeCoordsFromPlacement(rects, placement, rtl);
  let statefulPlacement = placement;
  let middlewareData = {};
  let resetCount = 0;
  for (let i19 = 0; i19 < validMiddleware.length; i19++) {
    const {
      name,
      fn
    } = validMiddleware[i19];
    const {
      x: nextX,
      y: nextY,
      data,
      reset
    } = await fn({
      x: x11,
      y: y8,
      initialPlacement: placement,
      placement: statefulPlacement,
      strategy,
      middlewareData,
      rects,
      platform: platform2,
      elements: {
        reference,
        floating
      }
    });
    x11 = nextX != null ? nextX : x11;
    y8 = nextY != null ? nextY : y8;
    middlewareData = {
      ...middlewareData,
      [name]: {
        ...middlewareData[name],
        ...data
      }
    };
    if (reset && resetCount <= 50) {
      resetCount++;
      if (typeof reset === "object") {
        if (reset.placement) {
          statefulPlacement = reset.placement;
        }
        if (reset.rects) {
          rects = reset.rects === true ? await platform2.getElementRects({
            reference,
            floating,
            strategy
          }) : reset.rects;
        }
        ({
          x: x11,
          y: y8
        } = computeCoordsFromPlacement(rects, statefulPlacement, rtl));
      }
      i19 = -1;
    }
  }
  return {
    x: x11,
    y: y8,
    placement: statefulPlacement,
    strategy,
    middlewareData
  };
};
async function detectOverflow(state, options) {
  var _await$platform$isEle;
  if (options === void 0) {
    options = {};
  }
  const {
    x: x11,
    y: y8,
    platform: platform2,
    rects,
    elements,
    strategy
  } = state;
  const {
    boundary = "clippingAncestors",
    rootBoundary = "viewport",
    elementContext = "floating",
    altBoundary = false,
    padding = 0
  } = evaluate(options, state);
  const paddingObject = getPaddingObject(padding);
  const altContext = elementContext === "floating" ? "reference" : "floating";
  const element = elements[altBoundary ? altContext : elementContext];
  const clippingClientRect = rectToClientRect(await platform2.getClippingRect({
    element: ((_await$platform$isEle = await (platform2.isElement == null ? void 0 : platform2.isElement(element))) != null ? _await$platform$isEle : true) ? element : element.contextElement || await (platform2.getDocumentElement == null ? void 0 : platform2.getDocumentElement(elements.floating)),
    boundary,
    rootBoundary,
    strategy
  }));
  const rect = elementContext === "floating" ? {
    x: x11,
    y: y8,
    width: rects.floating.width,
    height: rects.floating.height
  } : rects.reference;
  const offsetParent = await (platform2.getOffsetParent == null ? void 0 : platform2.getOffsetParent(elements.floating));
  const offsetScale = await (platform2.isElement == null ? void 0 : platform2.isElement(offsetParent)) ? await (platform2.getScale == null ? void 0 : platform2.getScale(offsetParent)) || {
    x: 1,
    y: 1
  } : {
    x: 1,
    y: 1
  };
  const elementClientRect = rectToClientRect(platform2.convertOffsetParentRelativeRectToViewportRelativeRect ? await platform2.convertOffsetParentRelativeRectToViewportRelativeRect({
    elements,
    rect,
    offsetParent,
    strategy
  }) : rect);
  return {
    top: (clippingClientRect.top - elementClientRect.top + paddingObject.top) / offsetScale.y,
    bottom: (elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom) / offsetScale.y,
    left: (clippingClientRect.left - elementClientRect.left + paddingObject.left) / offsetScale.x,
    right: (elementClientRect.right - clippingClientRect.right + paddingObject.right) / offsetScale.x
  };
}
var flip = function(options) {
  if (options === void 0) {
    options = {};
  }
  return {
    name: "flip",
    options,
    async fn(state) {
      var _middlewareData$arrow, _middlewareData$flip;
      const {
        placement,
        middlewareData,
        rects,
        initialPlacement,
        platform: platform2,
        elements
      } = state;
      const {
        mainAxis: checkMainAxis = true,
        crossAxis: checkCrossAxis = true,
        fallbackPlacements: specifiedFallbackPlacements,
        fallbackStrategy = "bestFit",
        fallbackAxisSideDirection = "none",
        flipAlignment = true,
        ...detectOverflowOptions
      } = evaluate(options, state);
      if ((_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
        return {};
      }
      const side = getSide(placement);
      const initialSideAxis = getSideAxis(initialPlacement);
      const isBasePlacement = getSide(initialPlacement) === initialPlacement;
      const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
      const fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipAlignment ? [getOppositePlacement(initialPlacement)] : getExpandedPlacements(initialPlacement));
      const hasFallbackAxisSideDirection = fallbackAxisSideDirection !== "none";
      if (!specifiedFallbackPlacements && hasFallbackAxisSideDirection) {
        fallbackPlacements.push(...getOppositeAxisPlacements(initialPlacement, flipAlignment, fallbackAxisSideDirection, rtl));
      }
      const placements2 = [initialPlacement, ...fallbackPlacements];
      const overflow = await detectOverflow(state, detectOverflowOptions);
      const overflows = [];
      let overflowsData = ((_middlewareData$flip = middlewareData.flip) == null ? void 0 : _middlewareData$flip.overflows) || [];
      if (checkMainAxis) {
        overflows.push(overflow[side]);
      }
      if (checkCrossAxis) {
        const sides2 = getAlignmentSides(placement, rects, rtl);
        overflows.push(overflow[sides2[0]], overflow[sides2[1]]);
      }
      overflowsData = [...overflowsData, {
        placement,
        overflows
      }];
      if (!overflows.every((side2) => side2 <= 0)) {
        var _middlewareData$flip2, _overflowsData$filter;
        const nextIndex = (((_middlewareData$flip2 = middlewareData.flip) == null ? void 0 : _middlewareData$flip2.index) || 0) + 1;
        const nextPlacement = placements2[nextIndex];
        if (nextPlacement) {
          return {
            data: {
              index: nextIndex,
              overflows: overflowsData
            },
            reset: {
              placement: nextPlacement
            }
          };
        }
        let resetPlacement = (_overflowsData$filter = overflowsData.filter((d14) => d14.overflows[0] <= 0).sort((a25, b11) => a25.overflows[1] - b11.overflows[1])[0]) == null ? void 0 : _overflowsData$filter.placement;
        if (!resetPlacement) {
          switch (fallbackStrategy) {
            case "bestFit": {
              var _overflowsData$filter2;
              const placement2 = (_overflowsData$filter2 = overflowsData.filter((d14) => {
                if (hasFallbackAxisSideDirection) {
                  const currentSideAxis = getSideAxis(d14.placement);
                  return currentSideAxis === initialSideAxis || // Create a bias to the `y` side axis due to horizontal
                  // reading directions favoring greater width.
                  currentSideAxis === "y";
                }
                return true;
              }).map((d14) => [d14.placement, d14.overflows.filter((overflow2) => overflow2 > 0).reduce((acc, overflow2) => acc + overflow2, 0)]).sort((a25, b11) => a25[1] - b11[1])[0]) == null ? void 0 : _overflowsData$filter2[0];
              if (placement2) {
                resetPlacement = placement2;
              }
              break;
            }
            case "initialPlacement":
              resetPlacement = initialPlacement;
              break;
          }
        }
        if (placement !== resetPlacement) {
          return {
            reset: {
              placement: resetPlacement
            }
          };
        }
      }
      return {};
    }
  };
};
async function convertValueToCoords(state, options) {
  const {
    placement,
    platform: platform2,
    elements
  } = state;
  const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
  const side = getSide(placement);
  const alignment = getAlignment(placement);
  const isVertical = getSideAxis(placement) === "y";
  const mainAxisMulti = ["left", "top"].includes(side) ? -1 : 1;
  const crossAxisMulti = rtl && isVertical ? -1 : 1;
  const rawValue = evaluate(options, state);
  let {
    mainAxis,
    crossAxis,
    alignmentAxis
  } = typeof rawValue === "number" ? {
    mainAxis: rawValue,
    crossAxis: 0,
    alignmentAxis: null
  } : {
    mainAxis: rawValue.mainAxis || 0,
    crossAxis: rawValue.crossAxis || 0,
    alignmentAxis: rawValue.alignmentAxis
  };
  if (alignment && typeof alignmentAxis === "number") {
    crossAxis = alignment === "end" ? alignmentAxis * -1 : alignmentAxis;
  }
  return isVertical ? {
    x: crossAxis * crossAxisMulti,
    y: mainAxis * mainAxisMulti
  } : {
    x: mainAxis * mainAxisMulti,
    y: crossAxis * crossAxisMulti
  };
}
var offset = function(options) {
  if (options === void 0) {
    options = 0;
  }
  return {
    name: "offset",
    options,
    async fn(state) {
      var _middlewareData$offse, _middlewareData$arrow;
      const {
        x: x11,
        y: y8,
        placement,
        middlewareData
      } = state;
      const diffCoords = await convertValueToCoords(state, options);
      if (placement === ((_middlewareData$offse = middlewareData.offset) == null ? void 0 : _middlewareData$offse.placement) && (_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
        return {};
      }
      return {
        x: x11 + diffCoords.x,
        y: y8 + diffCoords.y,
        data: {
          ...diffCoords,
          placement
        }
      };
    }
  };
};
var shift = function(options) {
  if (options === void 0) {
    options = {};
  }
  return {
    name: "shift",
    options,
    async fn(state) {
      const {
        x: x11,
        y: y8,
        placement
      } = state;
      const {
        mainAxis: checkMainAxis = true,
        crossAxis: checkCrossAxis = false,
        limiter = {
          fn: (_ref) => {
            let {
              x: x12,
              y: y9
            } = _ref;
            return {
              x: x12,
              y: y9
            };
          }
        },
        ...detectOverflowOptions
      } = evaluate(options, state);
      const coords = {
        x: x11,
        y: y8
      };
      const overflow = await detectOverflow(state, detectOverflowOptions);
      const crossAxis = getSideAxis(getSide(placement));
      const mainAxis = getOppositeAxis(crossAxis);
      let mainAxisCoord = coords[mainAxis];
      let crossAxisCoord = coords[crossAxis];
      if (checkMainAxis) {
        const minSide = mainAxis === "y" ? "top" : "left";
        const maxSide = mainAxis === "y" ? "bottom" : "right";
        const min2 = mainAxisCoord + overflow[minSide];
        const max2 = mainAxisCoord - overflow[maxSide];
        mainAxisCoord = clamp(min2, mainAxisCoord, max2);
      }
      if (checkCrossAxis) {
        const minSide = crossAxis === "y" ? "top" : "left";
        const maxSide = crossAxis === "y" ? "bottom" : "right";
        const min2 = crossAxisCoord + overflow[minSide];
        const max2 = crossAxisCoord - overflow[maxSide];
        crossAxisCoord = clamp(min2, crossAxisCoord, max2);
      }
      const limitedCoords = limiter.fn({
        ...state,
        [mainAxis]: mainAxisCoord,
        [crossAxis]: crossAxisCoord
      });
      return {
        ...limitedCoords,
        data: {
          x: limitedCoords.x - x11,
          y: limitedCoords.y - y8,
          enabled: {
            [mainAxis]: checkMainAxis,
            [crossAxis]: checkCrossAxis
          }
        }
      };
    }
  };
};
var size = function(options) {
  if (options === void 0) {
    options = {};
  }
  return {
    name: "size",
    options,
    async fn(state) {
      var _state$middlewareData, _state$middlewareData2;
      const {
        placement,
        rects,
        platform: platform2,
        elements
      } = state;
      const {
        apply = () => {
        },
        ...detectOverflowOptions
      } = evaluate(options, state);
      const overflow = await detectOverflow(state, detectOverflowOptions);
      const side = getSide(placement);
      const alignment = getAlignment(placement);
      const isYAxis = getSideAxis(placement) === "y";
      const {
        width,
        height
      } = rects.floating;
      let heightSide;
      let widthSide;
      if (side === "top" || side === "bottom") {
        heightSide = side;
        widthSide = alignment === (await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating)) ? "start" : "end") ? "left" : "right";
      } else {
        widthSide = side;
        heightSide = alignment === "end" ? "top" : "bottom";
      }
      const maximumClippingHeight = height - overflow.top - overflow.bottom;
      const maximumClippingWidth = width - overflow.left - overflow.right;
      const overflowAvailableHeight = min(height - overflow[heightSide], maximumClippingHeight);
      const overflowAvailableWidth = min(width - overflow[widthSide], maximumClippingWidth);
      const noShift = !state.middlewareData.shift;
      let availableHeight = overflowAvailableHeight;
      let availableWidth = overflowAvailableWidth;
      if ((_state$middlewareData = state.middlewareData.shift) != null && _state$middlewareData.enabled.x) {
        availableWidth = maximumClippingWidth;
      }
      if ((_state$middlewareData2 = state.middlewareData.shift) != null && _state$middlewareData2.enabled.y) {
        availableHeight = maximumClippingHeight;
      }
      if (noShift && !alignment) {
        const xMin = max(overflow.left, 0);
        const xMax = max(overflow.right, 0);
        const yMin = max(overflow.top, 0);
        const yMax = max(overflow.bottom, 0);
        if (isYAxis) {
          availableWidth = width - 2 * (xMin !== 0 || xMax !== 0 ? xMin + xMax : max(overflow.left, overflow.right));
        } else {
          availableHeight = height - 2 * (yMin !== 0 || yMax !== 0 ? yMin + yMax : max(overflow.top, overflow.bottom));
        }
      }
      await apply({
        ...state,
        availableWidth,
        availableHeight
      });
      const nextDimensions = await platform2.getDimensions(elements.floating);
      if (width !== nextDimensions.width || height !== nextDimensions.height) {
        return {
          reset: {
            rects: true
          }
        };
      }
      return {};
    }
  };
};

// node_modules/@floating-ui/dom/dist/floating-ui.dom.mjs
function getCssDimensions(element) {
  const css = getComputedStyle2(element);
  let width = parseFloat(css.width) || 0;
  let height = parseFloat(css.height) || 0;
  const hasOffset = isHTMLElement(element);
  const offsetWidth = hasOffset ? element.offsetWidth : width;
  const offsetHeight = hasOffset ? element.offsetHeight : height;
  const shouldFallback = round(width) !== offsetWidth || round(height) !== offsetHeight;
  if (shouldFallback) {
    width = offsetWidth;
    height = offsetHeight;
  }
  return {
    width,
    height,
    $: shouldFallback
  };
}
function unwrapElement(element) {
  return !isElement(element) ? element.contextElement : element;
}
function getScale(element) {
  const domElement = unwrapElement(element);
  if (!isHTMLElement(domElement)) {
    return createCoords(1);
  }
  const rect = domElement.getBoundingClientRect();
  const {
    width,
    height,
    $: $4
  } = getCssDimensions(domElement);
  let x11 = ($4 ? round(rect.width) : rect.width) / width;
  let y8 = ($4 ? round(rect.height) : rect.height) / height;
  if (!x11 || !Number.isFinite(x11)) {
    x11 = 1;
  }
  if (!y8 || !Number.isFinite(y8)) {
    y8 = 1;
  }
  return {
    x: x11,
    y: y8
  };
}
var noOffsets = createCoords(0);
function getVisualOffsets(element) {
  const win = getWindow(element);
  if (!isWebKit() || !win.visualViewport) {
    return noOffsets;
  }
  return {
    x: win.visualViewport.offsetLeft,
    y: win.visualViewport.offsetTop
  };
}
function shouldAddVisualOffsets(element, isFixed, floatingOffsetParent) {
  if (isFixed === void 0) {
    isFixed = false;
  }
  if (!floatingOffsetParent || isFixed && floatingOffsetParent !== getWindow(element)) {
    return false;
  }
  return isFixed;
}
function getBoundingClientRect(element, includeScale, isFixedStrategy, offsetParent) {
  if (includeScale === void 0) {
    includeScale = false;
  }
  if (isFixedStrategy === void 0) {
    isFixedStrategy = false;
  }
  const clientRect = element.getBoundingClientRect();
  const domElement = unwrapElement(element);
  let scale = createCoords(1);
  if (includeScale) {
    if (offsetParent) {
      if (isElement(offsetParent)) {
        scale = getScale(offsetParent);
      }
    } else {
      scale = getScale(element);
    }
  }
  const visualOffsets = shouldAddVisualOffsets(domElement, isFixedStrategy, offsetParent) ? getVisualOffsets(domElement) : createCoords(0);
  let x11 = (clientRect.left + visualOffsets.x) / scale.x;
  let y8 = (clientRect.top + visualOffsets.y) / scale.y;
  let width = clientRect.width / scale.x;
  let height = clientRect.height / scale.y;
  if (domElement) {
    const win = getWindow(domElement);
    const offsetWin = offsetParent && isElement(offsetParent) ? getWindow(offsetParent) : offsetParent;
    let currentWin = win;
    let currentIFrame = getFrameElement(currentWin);
    while (currentIFrame && offsetParent && offsetWin !== currentWin) {
      const iframeScale = getScale(currentIFrame);
      const iframeRect = currentIFrame.getBoundingClientRect();
      const css = getComputedStyle2(currentIFrame);
      const left = iframeRect.left + (currentIFrame.clientLeft + parseFloat(css.paddingLeft)) * iframeScale.x;
      const top = iframeRect.top + (currentIFrame.clientTop + parseFloat(css.paddingTop)) * iframeScale.y;
      x11 *= iframeScale.x;
      y8 *= iframeScale.y;
      width *= iframeScale.x;
      height *= iframeScale.y;
      x11 += left;
      y8 += top;
      currentWin = getWindow(currentIFrame);
      currentIFrame = getFrameElement(currentWin);
    }
  }
  return rectToClientRect({
    width,
    height,
    x: x11,
    y: y8
  });
}
function getWindowScrollBarX(element, rect) {
  const leftScroll = getNodeScroll(element).scrollLeft;
  if (!rect) {
    return getBoundingClientRect(getDocumentElement(element)).left + leftScroll;
  }
  return rect.left + leftScroll;
}
function getHTMLOffset(documentElement, scroll, ignoreScrollbarX) {
  if (ignoreScrollbarX === void 0) {
    ignoreScrollbarX = false;
  }
  const htmlRect = documentElement.getBoundingClientRect();
  const x11 = htmlRect.left + scroll.scrollLeft - (ignoreScrollbarX ? 0 : (
    // RTL <body> scrollbar.
    getWindowScrollBarX(documentElement, htmlRect)
  ));
  const y8 = htmlRect.top + scroll.scrollTop;
  return {
    x: x11,
    y: y8
  };
}
function convertOffsetParentRelativeRectToViewportRelativeRect(_ref) {
  let {
    elements,
    rect,
    offsetParent,
    strategy
  } = _ref;
  const isFixed = strategy === "fixed";
  const documentElement = getDocumentElement(offsetParent);
  const topLayer = elements ? isTopLayer(elements.floating) : false;
  if (offsetParent === documentElement || topLayer && isFixed) {
    return rect;
  }
  let scroll = {
    scrollLeft: 0,
    scrollTop: 0
  };
  let scale = createCoords(1);
  const offsets = createCoords(0);
  const isOffsetParentAnElement = isHTMLElement(offsetParent);
  if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
    if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
      scroll = getNodeScroll(offsetParent);
    }
    if (isHTMLElement(offsetParent)) {
      const offsetRect = getBoundingClientRect(offsetParent);
      scale = getScale(offsetParent);
      offsets.x = offsetRect.x + offsetParent.clientLeft;
      offsets.y = offsetRect.y + offsetParent.clientTop;
    }
  }
  const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll, true) : createCoords(0);
  return {
    width: rect.width * scale.x,
    height: rect.height * scale.y,
    x: rect.x * scale.x - scroll.scrollLeft * scale.x + offsets.x + htmlOffset.x,
    y: rect.y * scale.y - scroll.scrollTop * scale.y + offsets.y + htmlOffset.y
  };
}
function getClientRects(element) {
  return Array.from(element.getClientRects());
}
function getDocumentRect(element) {
  const html = getDocumentElement(element);
  const scroll = getNodeScroll(element);
  const body = element.ownerDocument.body;
  const width = max(html.scrollWidth, html.clientWidth, body.scrollWidth, body.clientWidth);
  const height = max(html.scrollHeight, html.clientHeight, body.scrollHeight, body.clientHeight);
  let x11 = -scroll.scrollLeft + getWindowScrollBarX(element);
  const y8 = -scroll.scrollTop;
  if (getComputedStyle2(body).direction === "rtl") {
    x11 += max(html.clientWidth, body.clientWidth) - width;
  }
  return {
    width,
    height,
    x: x11,
    y: y8
  };
}
function getViewportRect(element, strategy) {
  const win = getWindow(element);
  const html = getDocumentElement(element);
  const visualViewport = win.visualViewport;
  let width = html.clientWidth;
  let height = html.clientHeight;
  let x11 = 0;
  let y8 = 0;
  if (visualViewport) {
    width = visualViewport.width;
    height = visualViewport.height;
    const visualViewportBased = isWebKit();
    if (!visualViewportBased || visualViewportBased && strategy === "fixed") {
      x11 = visualViewport.offsetLeft;
      y8 = visualViewport.offsetTop;
    }
  }
  return {
    width,
    height,
    x: x11,
    y: y8
  };
}
function getInnerBoundingClientRect(element, strategy) {
  const clientRect = getBoundingClientRect(element, true, strategy === "fixed");
  const top = clientRect.top + element.clientTop;
  const left = clientRect.left + element.clientLeft;
  const scale = isHTMLElement(element) ? getScale(element) : createCoords(1);
  const width = element.clientWidth * scale.x;
  const height = element.clientHeight * scale.y;
  const x11 = left * scale.x;
  const y8 = top * scale.y;
  return {
    width,
    height,
    x: x11,
    y: y8
  };
}
function getClientRectFromClippingAncestor(element, clippingAncestor, strategy) {
  let rect;
  if (clippingAncestor === "viewport") {
    rect = getViewportRect(element, strategy);
  } else if (clippingAncestor === "document") {
    rect = getDocumentRect(getDocumentElement(element));
  } else if (isElement(clippingAncestor)) {
    rect = getInnerBoundingClientRect(clippingAncestor, strategy);
  } else {
    const visualOffsets = getVisualOffsets(element);
    rect = {
      x: clippingAncestor.x - visualOffsets.x,
      y: clippingAncestor.y - visualOffsets.y,
      width: clippingAncestor.width,
      height: clippingAncestor.height
    };
  }
  return rectToClientRect(rect);
}
function hasFixedPositionAncestor(element, stopNode) {
  const parentNode = getParentNode(element);
  if (parentNode === stopNode || !isElement(parentNode) || isLastTraversableNode(parentNode)) {
    return false;
  }
  return getComputedStyle2(parentNode).position === "fixed" || hasFixedPositionAncestor(parentNode, stopNode);
}
function getClippingElementAncestors(element, cache) {
  const cachedResult = cache.get(element);
  if (cachedResult) {
    return cachedResult;
  }
  let result = getOverflowAncestors(element, [], false).filter((el) => isElement(el) && getNodeName(el) !== "body");
  let currentContainingBlockComputedStyle = null;
  const elementIsFixed = getComputedStyle2(element).position === "fixed";
  let currentNode = elementIsFixed ? getParentNode(element) : element;
  while (isElement(currentNode) && !isLastTraversableNode(currentNode)) {
    const computedStyle = getComputedStyle2(currentNode);
    const currentNodeIsContaining = isContainingBlock(currentNode);
    if (!currentNodeIsContaining && computedStyle.position === "fixed") {
      currentContainingBlockComputedStyle = null;
    }
    const shouldDropCurrentNode = elementIsFixed ? !currentNodeIsContaining && !currentContainingBlockComputedStyle : !currentNodeIsContaining && computedStyle.position === "static" && !!currentContainingBlockComputedStyle && ["absolute", "fixed"].includes(currentContainingBlockComputedStyle.position) || isOverflowElement(currentNode) && !currentNodeIsContaining && hasFixedPositionAncestor(element, currentNode);
    if (shouldDropCurrentNode) {
      result = result.filter((ancestor) => ancestor !== currentNode);
    } else {
      currentContainingBlockComputedStyle = computedStyle;
    }
    currentNode = getParentNode(currentNode);
  }
  cache.set(element, result);
  return result;
}
function getClippingRect(_ref) {
  let {
    element,
    boundary,
    rootBoundary,
    strategy
  } = _ref;
  const elementClippingAncestors = boundary === "clippingAncestors" ? isTopLayer(element) ? [] : getClippingElementAncestors(element, this._c) : [].concat(boundary);
  const clippingAncestors = [...elementClippingAncestors, rootBoundary];
  const firstClippingAncestor = clippingAncestors[0];
  const clippingRect = clippingAncestors.reduce((accRect, clippingAncestor) => {
    const rect = getClientRectFromClippingAncestor(element, clippingAncestor, strategy);
    accRect.top = max(rect.top, accRect.top);
    accRect.right = min(rect.right, accRect.right);
    accRect.bottom = min(rect.bottom, accRect.bottom);
    accRect.left = max(rect.left, accRect.left);
    return accRect;
  }, getClientRectFromClippingAncestor(element, firstClippingAncestor, strategy));
  return {
    width: clippingRect.right - clippingRect.left,
    height: clippingRect.bottom - clippingRect.top,
    x: clippingRect.left,
    y: clippingRect.top
  };
}
function getDimensions(element) {
  const {
    width,
    height
  } = getCssDimensions(element);
  return {
    width,
    height
  };
}
function getRectRelativeToOffsetParent(element, offsetParent, strategy) {
  const isOffsetParentAnElement = isHTMLElement(offsetParent);
  const documentElement = getDocumentElement(offsetParent);
  const isFixed = strategy === "fixed";
  const rect = getBoundingClientRect(element, true, isFixed, offsetParent);
  let scroll = {
    scrollLeft: 0,
    scrollTop: 0
  };
  const offsets = createCoords(0);
  if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
    if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
      scroll = getNodeScroll(offsetParent);
    }
    if (isOffsetParentAnElement) {
      const offsetRect = getBoundingClientRect(offsetParent, true, isFixed, offsetParent);
      offsets.x = offsetRect.x + offsetParent.clientLeft;
      offsets.y = offsetRect.y + offsetParent.clientTop;
    } else if (documentElement) {
      offsets.x = getWindowScrollBarX(documentElement);
    }
  }
  const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll) : createCoords(0);
  const x11 = rect.left + scroll.scrollLeft - offsets.x - htmlOffset.x;
  const y8 = rect.top + scroll.scrollTop - offsets.y - htmlOffset.y;
  return {
    x: x11,
    y: y8,
    width: rect.width,
    height: rect.height
  };
}
function isStaticPositioned(element) {
  return getComputedStyle2(element).position === "static";
}
function getTrueOffsetParent(element, polyfill) {
  if (!isHTMLElement(element) || getComputedStyle2(element).position === "fixed") {
    return null;
  }
  if (polyfill) {
    return polyfill(element);
  }
  let rawOffsetParent = element.offsetParent;
  if (getDocumentElement(element) === rawOffsetParent) {
    rawOffsetParent = rawOffsetParent.ownerDocument.body;
  }
  return rawOffsetParent;
}
function getOffsetParent(element, polyfill) {
  const win = getWindow(element);
  if (isTopLayer(element)) {
    return win;
  }
  if (!isHTMLElement(element)) {
    let svgOffsetParent = getParentNode(element);
    while (svgOffsetParent && !isLastTraversableNode(svgOffsetParent)) {
      if (isElement(svgOffsetParent) && !isStaticPositioned(svgOffsetParent)) {
        return svgOffsetParent;
      }
      svgOffsetParent = getParentNode(svgOffsetParent);
    }
    return win;
  }
  let offsetParent = getTrueOffsetParent(element, polyfill);
  while (offsetParent && isTableElement(offsetParent) && isStaticPositioned(offsetParent)) {
    offsetParent = getTrueOffsetParent(offsetParent, polyfill);
  }
  if (offsetParent && isLastTraversableNode(offsetParent) && isStaticPositioned(offsetParent) && !isContainingBlock(offsetParent)) {
    return win;
  }
  return offsetParent || getContainingBlock(element) || win;
}
var getElementRects = async function(data) {
  const getOffsetParentFn = this.getOffsetParent || getOffsetParent;
  const getDimensionsFn = this.getDimensions;
  const floatingDimensions = await getDimensionsFn(data.floating);
  return {
    reference: getRectRelativeToOffsetParent(data.reference, await getOffsetParentFn(data.floating), data.strategy),
    floating: {
      x: 0,
      y: 0,
      width: floatingDimensions.width,
      height: floatingDimensions.height
    }
  };
};
function isRTL(element) {
  return getComputedStyle2(element).direction === "rtl";
}
var platform = {
  convertOffsetParentRelativeRectToViewportRelativeRect,
  getDocumentElement,
  getClippingRect,
  getOffsetParent,
  getElementRects,
  getClientRects,
  getDimensions,
  getScale,
  isElement,
  isRTL
};
function rectsAreEqual(a25, b11) {
  return a25.x === b11.x && a25.y === b11.y && a25.width === b11.width && a25.height === b11.height;
}
function observeMove(element, onMove) {
  let io = null;
  let timeoutId2;
  const root = getDocumentElement(element);
  function cleanup2() {
    var _io;
    clearTimeout(timeoutId2);
    (_io = io) == null || _io.disconnect();
    io = null;
  }
  function refresh(skip, threshold) {
    if (skip === void 0) {
      skip = false;
    }
    if (threshold === void 0) {
      threshold = 1;
    }
    cleanup2();
    const elementRectForRootMargin = element.getBoundingClientRect();
    const {
      left,
      top,
      width,
      height
    } = elementRectForRootMargin;
    if (!skip) {
      onMove();
    }
    if (!width || !height) {
      return;
    }
    const insetTop = floor(top);
    const insetRight = floor(root.clientWidth - (left + width));
    const insetBottom = floor(root.clientHeight - (top + height));
    const insetLeft = floor(left);
    const rootMargin = -insetTop + "px " + -insetRight + "px " + -insetBottom + "px " + -insetLeft + "px";
    const options = {
      rootMargin,
      threshold: max(0, min(1, threshold)) || 1
    };
    let isFirstUpdate = true;
    function handleObserve(entries) {
      const ratio = entries[0].intersectionRatio;
      if (ratio !== threshold) {
        if (!isFirstUpdate) {
          return refresh();
        }
        if (!ratio) {
          timeoutId2 = setTimeout(() => {
            refresh(false, 1e-7);
          }, 1e3);
        } else {
          refresh(false, ratio);
        }
      }
      if (ratio === 1 && !rectsAreEqual(elementRectForRootMargin, element.getBoundingClientRect())) {
        refresh();
      }
      isFirstUpdate = false;
    }
    try {
      io = new IntersectionObserver(handleObserve, {
        ...options,
        // Handle <iframe>s
        root: root.ownerDocument
      });
    } catch (e8) {
      io = new IntersectionObserver(handleObserve, options);
    }
    io.observe(element);
  }
  refresh(true);
  return cleanup2;
}
function autoUpdate(reference, floating, update, options) {
  if (options === void 0) {
    options = {};
  }
  const {
    ancestorScroll = true,
    ancestorResize = true,
    elementResize = typeof ResizeObserver === "function",
    layoutShift = typeof IntersectionObserver === "function",
    animationFrame = false
  } = options;
  const referenceEl = unwrapElement(reference);
  const ancestors = ancestorScroll || ancestorResize ? [...referenceEl ? getOverflowAncestors(referenceEl) : [], ...getOverflowAncestors(floating)] : [];
  ancestors.forEach((ancestor) => {
    ancestorScroll && ancestor.addEventListener("scroll", update, {
      passive: true
    });
    ancestorResize && ancestor.addEventListener("resize", update);
  });
  const cleanupIo = referenceEl && layoutShift ? observeMove(referenceEl, update) : null;
  let reobserveFrame = -1;
  let resizeObserver = null;
  if (elementResize) {
    resizeObserver = new ResizeObserver((_ref) => {
      let [firstEntry] = _ref;
      if (firstEntry && firstEntry.target === referenceEl && resizeObserver) {
        resizeObserver.unobserve(floating);
        cancelAnimationFrame(reobserveFrame);
        reobserveFrame = requestAnimationFrame(() => {
          var _resizeObserver;
          (_resizeObserver = resizeObserver) == null || _resizeObserver.observe(floating);
        });
      }
      update();
    });
    if (referenceEl && !animationFrame) {
      resizeObserver.observe(referenceEl);
    }
    resizeObserver.observe(floating);
  }
  let frameId;
  let prevRefRect = animationFrame ? getBoundingClientRect(reference) : null;
  if (animationFrame) {
    frameLoop();
  }
  function frameLoop() {
    const nextRefRect = getBoundingClientRect(reference);
    if (prevRefRect && !rectsAreEqual(prevRefRect, nextRefRect)) {
      update();
    }
    prevRefRect = nextRefRect;
    frameId = requestAnimationFrame(frameLoop);
  }
  update();
  return () => {
    var _resizeObserver2;
    ancestors.forEach((ancestor) => {
      ancestorScroll && ancestor.removeEventListener("scroll", update);
      ancestorResize && ancestor.removeEventListener("resize", update);
    });
    cleanupIo == null || cleanupIo();
    (_resizeObserver2 = resizeObserver) == null || _resizeObserver2.disconnect();
    resizeObserver = null;
    if (animationFrame) {
      cancelAnimationFrame(frameId);
    }
  };
}
var detectOverflow2 = detectOverflow;
var offset2 = offset;
var shift2 = shift;
var flip2 = flip;
var size2 = size;
var computePosition2 = (reference, floating, options) => {
  const cache = /* @__PURE__ */ new Map();
  const mergedOptions = {
    platform,
    ...options
  };
  const platformWithCache = {
    ...mergedOptions.platform,
    _c: cache
  };
  return computePosition(reference, floating, {
    ...mergedOptions,
    platform: platformWithCache
  });
};

// node_modules/@floating-ui/react-dom/dist/floating-ui.react-dom.mjs
var React2 = __toESM(require_react(), 1);
var import_react76 = __toESM(require_react(), 1);
var ReactDOM = __toESM(require_react_dom(), 1);
var index = typeof document !== "undefined" ? import_react76.useLayoutEffect : import_react76.useEffect;
function deepEqual(a25, b11) {
  if (a25 === b11) {
    return true;
  }
  if (typeof a25 !== typeof b11) {
    return false;
  }
  if (typeof a25 === "function" && a25.toString() === b11.toString()) {
    return true;
  }
  let length;
  let i19;
  let keys;
  if (a25 && b11 && typeof a25 === "object") {
    if (Array.isArray(a25)) {
      length = a25.length;
      if (length !== b11.length) return false;
      for (i19 = length; i19-- !== 0; ) {
        if (!deepEqual(a25[i19], b11[i19])) {
          return false;
        }
      }
      return true;
    }
    keys = Object.keys(a25);
    length = keys.length;
    if (length !== Object.keys(b11).length) {
      return false;
    }
    for (i19 = length; i19-- !== 0; ) {
      if (!{}.hasOwnProperty.call(b11, keys[i19])) {
        return false;
      }
    }
    for (i19 = length; i19-- !== 0; ) {
      const key = keys[i19];
      if (key === "_owner" && a25.$$typeof) {
        continue;
      }
      if (!deepEqual(a25[key], b11[key])) {
        return false;
      }
    }
    return true;
  }
  return a25 !== a25 && b11 !== b11;
}
function getDPR(element) {
  if (typeof window === "undefined") {
    return 1;
  }
  const win = element.ownerDocument.defaultView || window;
  return win.devicePixelRatio || 1;
}
function roundByDPR(element, value) {
  const dpr = getDPR(element);
  return Math.round(value * dpr) / dpr;
}
function useLatestRef(value) {
  const ref = React2.useRef(value);
  index(() => {
    ref.current = value;
  });
  return ref;
}
function useFloating(options) {
  if (options === void 0) {
    options = {};
  }
  const {
    placement = "bottom",
    strategy = "absolute",
    middleware = [],
    platform: platform2,
    elements: {
      reference: externalReference,
      floating: externalFloating
    } = {},
    transform = true,
    whileElementsMounted,
    open
  } = options;
  const [data, setData] = React2.useState({
    x: 0,
    y: 0,
    strategy,
    placement,
    middlewareData: {},
    isPositioned: false
  });
  const [latestMiddleware, setLatestMiddleware] = React2.useState(middleware);
  if (!deepEqual(latestMiddleware, middleware)) {
    setLatestMiddleware(middleware);
  }
  const [_reference, _setReference] = React2.useState(null);
  const [_floating, _setFloating] = React2.useState(null);
  const setReference = React2.useCallback((node) => {
    if (node !== referenceRef.current) {
      referenceRef.current = node;
      _setReference(node);
    }
  }, []);
  const setFloating = React2.useCallback((node) => {
    if (node !== floatingRef.current) {
      floatingRef.current = node;
      _setFloating(node);
    }
  }, []);
  const referenceEl = externalReference || _reference;
  const floatingEl = externalFloating || _floating;
  const referenceRef = React2.useRef(null);
  const floatingRef = React2.useRef(null);
  const dataRef = React2.useRef(data);
  const hasWhileElementsMounted = whileElementsMounted != null;
  const whileElementsMountedRef = useLatestRef(whileElementsMounted);
  const platformRef = useLatestRef(platform2);
  const openRef = useLatestRef(open);
  const update = React2.useCallback(() => {
    if (!referenceRef.current || !floatingRef.current) {
      return;
    }
    const config = {
      placement,
      strategy,
      middleware: latestMiddleware
    };
    if (platformRef.current) {
      config.platform = platformRef.current;
    }
    computePosition2(referenceRef.current, floatingRef.current, config).then((data2) => {
      const fullData = {
        ...data2,
        // The floating element's position may be recomputed while it's closed
        // but still mounted (such as when transitioning out). To ensure
        // `isPositioned` will be `false` initially on the next open, avoid
        // setting it to `true` when `open === false` (must be specified).
        isPositioned: openRef.current !== false
      };
      if (isMountedRef.current && !deepEqual(dataRef.current, fullData)) {
        dataRef.current = fullData;
        ReactDOM.flushSync(() => {
          setData(fullData);
        });
      }
    });
  }, [latestMiddleware, placement, strategy, platformRef, openRef]);
  index(() => {
    if (open === false && dataRef.current.isPositioned) {
      dataRef.current.isPositioned = false;
      setData((data2) => ({
        ...data2,
        isPositioned: false
      }));
    }
  }, [open]);
  const isMountedRef = React2.useRef(false);
  index(() => {
    isMountedRef.current = true;
    return () => {
      isMountedRef.current = false;
    };
  }, []);
  index(() => {
    if (referenceEl) referenceRef.current = referenceEl;
    if (floatingEl) floatingRef.current = floatingEl;
    if (referenceEl && floatingEl) {
      if (whileElementsMountedRef.current) {
        return whileElementsMountedRef.current(referenceEl, floatingEl, update);
      }
      update();
    }
  }, [referenceEl, floatingEl, update, whileElementsMountedRef, hasWhileElementsMounted]);
  const refs = React2.useMemo(() => ({
    reference: referenceRef,
    floating: floatingRef,
    setReference,
    setFloating
  }), [setReference, setFloating]);
  const elements = React2.useMemo(() => ({
    reference: referenceEl,
    floating: floatingEl
  }), [referenceEl, floatingEl]);
  const floatingStyles = React2.useMemo(() => {
    const initialStyles = {
      position: strategy,
      left: 0,
      top: 0
    };
    if (!elements.floating) {
      return initialStyles;
    }
    const x11 = roundByDPR(elements.floating, data.x);
    const y8 = roundByDPR(elements.floating, data.y);
    if (transform) {
      return {
        ...initialStyles,
        transform: "translate(" + x11 + "px, " + y8 + "px)",
        ...getDPR(elements.floating) >= 1.5 && {
          willChange: "transform"
        }
      };
    }
    return {
      position: strategy,
      left: x11,
      top: y8
    };
  }, [strategy, transform, elements.floating, data.x, data.y]);
  return React2.useMemo(() => ({
    ...data,
    update,
    refs,
    elements,
    floatingStyles
  }), [data, update, refs, elements, floatingStyles]);
}
var offset3 = (options, deps) => ({
  ...offset2(options),
  options: [options, deps]
});
var shift3 = (options, deps) => ({
  ...shift2(options),
  options: [options, deps]
});
var flip3 = (options, deps) => ({
  ...flip2(options),
  options: [options, deps]
});
var size3 = (options, deps) => ({
  ...size2(options),
  options: [options, deps]
});

// node_modules/@floating-ui/react/dist/floating-ui.react.mjs
function useMergeRefs(refs) {
  return React3.useMemo(() => {
    if (refs.every((ref) => ref == null)) {
      return null;
    }
    return (value) => {
      refs.forEach((ref) => {
        if (typeof ref === "function") {
          ref(value);
        } else if (ref != null) {
          ref.current = value;
        }
      });
    };
  }, refs);
}
var SafeReact = {
  ...React3
};
var useInsertionEffect = SafeReact.useInsertionEffect;
var useSafeInsertionEffect = useInsertionEffect || ((fn) => fn());
function useEffectEvent(callback) {
  const ref = React3.useRef(() => {
    if (true) {
      throw new Error("Cannot call an event handler while rendering.");
    }
  });
  useSafeInsertionEffect(() => {
    ref.current = callback;
  });
  return React3.useCallback(function() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    return ref.current == null ? void 0 : ref.current(...args);
  }, []);
}
var ARROW_UP = "ArrowUp";
var ARROW_DOWN = "ArrowDown";
var ARROW_LEFT = "ArrowLeft";
var ARROW_RIGHT = "ArrowRight";
function isDifferentRow(index3, cols, prevRow) {
  return Math.floor(index3 / cols) !== prevRow;
}
function isIndexOutOfBounds(listRef, index3) {
  return index3 < 0 || index3 >= listRef.current.length;
}
function getMinIndex(listRef, disabledIndices) {
  return findNonDisabledIndex(listRef, {
    disabledIndices
  });
}
function getMaxIndex(listRef, disabledIndices) {
  return findNonDisabledIndex(listRef, {
    decrement: true,
    startingIndex: listRef.current.length,
    disabledIndices
  });
}
function findNonDisabledIndex(listRef, _temp) {
  let {
    startingIndex = -1,
    decrement = false,
    disabledIndices,
    amount = 1
  } = _temp === void 0 ? {} : _temp;
  const list = listRef.current;
  let index3 = startingIndex;
  do {
    index3 += decrement ? -amount : amount;
  } while (index3 >= 0 && index3 <= list.length - 1 && isDisabled(list, index3, disabledIndices));
  return index3;
}
function getGridNavigatedIndex(elementsRef, _ref) {
  let {
    event,
    orientation,
    loop,
    rtl,
    cols,
    disabledIndices,
    minIndex,
    maxIndex,
    prevIndex,
    stopEvent: stop = false
  } = _ref;
  let nextIndex = prevIndex;
  if (event.key === ARROW_UP) {
    stop && stopEvent(event);
    if (prevIndex === -1) {
      nextIndex = maxIndex;
    } else {
      nextIndex = findNonDisabledIndex(elementsRef, {
        startingIndex: nextIndex,
        amount: cols,
        decrement: true,
        disabledIndices
      });
      if (loop && (prevIndex - cols < minIndex || nextIndex < 0)) {
        const col = prevIndex % cols;
        const maxCol = maxIndex % cols;
        const offset4 = maxIndex - (maxCol - col);
        if (maxCol === col) {
          nextIndex = maxIndex;
        } else {
          nextIndex = maxCol > col ? offset4 : offset4 - cols;
        }
      }
    }
    if (isIndexOutOfBounds(elementsRef, nextIndex)) {
      nextIndex = prevIndex;
    }
  }
  if (event.key === ARROW_DOWN) {
    stop && stopEvent(event);
    if (prevIndex === -1) {
      nextIndex = minIndex;
    } else {
      nextIndex = findNonDisabledIndex(elementsRef, {
        startingIndex: prevIndex,
        amount: cols,
        disabledIndices
      });
      if (loop && prevIndex + cols > maxIndex) {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex % cols - cols,
          amount: cols,
          disabledIndices
        });
      }
    }
    if (isIndexOutOfBounds(elementsRef, nextIndex)) {
      nextIndex = prevIndex;
    }
  }
  if (orientation === "both") {
    const prevRow = floor(prevIndex / cols);
    if (event.key === (rtl ? ARROW_LEFT : ARROW_RIGHT)) {
      stop && stopEvent(event);
      if (prevIndex % cols !== cols - 1) {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex,
          disabledIndices
        });
        if (loop && isDifferentRow(nextIndex, cols, prevRow)) {
          nextIndex = findNonDisabledIndex(elementsRef, {
            startingIndex: prevIndex - prevIndex % cols - 1,
            disabledIndices
          });
        }
      } else if (loop) {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex - prevIndex % cols - 1,
          disabledIndices
        });
      }
      if (isDifferentRow(nextIndex, cols, prevRow)) {
        nextIndex = prevIndex;
      }
    }
    if (event.key === (rtl ? ARROW_RIGHT : ARROW_LEFT)) {
      stop && stopEvent(event);
      if (prevIndex % cols !== 0) {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex,
          decrement: true,
          disabledIndices
        });
        if (loop && isDifferentRow(nextIndex, cols, prevRow)) {
          nextIndex = findNonDisabledIndex(elementsRef, {
            startingIndex: prevIndex + (cols - prevIndex % cols),
            decrement: true,
            disabledIndices
          });
        }
      } else if (loop) {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex + (cols - prevIndex % cols),
          decrement: true,
          disabledIndices
        });
      }
      if (isDifferentRow(nextIndex, cols, prevRow)) {
        nextIndex = prevIndex;
      }
    }
    const lastRow = floor(maxIndex / cols) === prevRow;
    if (isIndexOutOfBounds(elementsRef, nextIndex)) {
      if (loop && lastRow) {
        nextIndex = event.key === (rtl ? ARROW_RIGHT : ARROW_LEFT) ? maxIndex : findNonDisabledIndex(elementsRef, {
          startingIndex: prevIndex - prevIndex % cols - 1,
          disabledIndices
        });
      } else {
        nextIndex = prevIndex;
      }
    }
  }
  return nextIndex;
}
function buildCellMap(sizes, cols, dense) {
  const cellMap = [];
  let startIndex = 0;
  sizes.forEach((_ref2, index3) => {
    let {
      width,
      height
    } = _ref2;
    if (width > cols) {
      if (true) {
        throw new Error("[Floating UI]: Invalid grid - item width at index " + index3 + " is greater than grid columns");
      }
    }
    let itemPlaced = false;
    if (dense) {
      startIndex = 0;
    }
    while (!itemPlaced) {
      const targetCells = [];
      for (let i19 = 0; i19 < width; i19++) {
        for (let j8 = 0; j8 < height; j8++) {
          targetCells.push(startIndex + i19 + j8 * cols);
        }
      }
      if (startIndex % cols + width <= cols && targetCells.every((cell) => cellMap[cell] == null)) {
        targetCells.forEach((cell) => {
          cellMap[cell] = index3;
        });
        itemPlaced = true;
      } else {
        startIndex++;
      }
    }
  });
  return [...cellMap];
}
function getCellIndexOfCorner(index3, sizes, cellMap, cols, corner) {
  if (index3 === -1) return -1;
  const firstCellIndex = cellMap.indexOf(index3);
  const sizeItem = sizes[index3];
  switch (corner) {
    case "tl":
      return firstCellIndex;
    case "tr":
      if (!sizeItem) {
        return firstCellIndex;
      }
      return firstCellIndex + sizeItem.width - 1;
    case "bl":
      if (!sizeItem) {
        return firstCellIndex;
      }
      return firstCellIndex + (sizeItem.height - 1) * cols;
    case "br":
      return cellMap.lastIndexOf(index3);
  }
}
function getCellIndices(indices, cellMap) {
  return cellMap.flatMap((index3, cellIndex) => indices.includes(index3) ? [cellIndex] : []);
}
function isDisabled(list, index3, disabledIndices) {
  if (disabledIndices) {
    return disabledIndices.includes(index3);
  }
  const element = list[index3];
  return element == null || element.hasAttribute("disabled") || element.getAttribute("aria-disabled") === "true";
}
var index2 = typeof document !== "undefined" ? import_react77.useLayoutEffect : import_react77.useEffect;
function sortByDocumentPosition(a25, b11) {
  const position = a25.compareDocumentPosition(b11);
  if (position & Node.DOCUMENT_POSITION_FOLLOWING || position & Node.DOCUMENT_POSITION_CONTAINED_BY) {
    return -1;
  }
  if (position & Node.DOCUMENT_POSITION_PRECEDING || position & Node.DOCUMENT_POSITION_CONTAINS) {
    return 1;
  }
  return 0;
}
function areMapsEqual(map1, map2) {
  if (map1.size !== map2.size) {
    return false;
  }
  for (const [key, value] of map1.entries()) {
    if (value !== map2.get(key)) {
      return false;
    }
  }
  return true;
}
var FloatingListContext = React3.createContext({
  register: () => {
  },
  unregister: () => {
  },
  map: /* @__PURE__ */ new Map(),
  elementsRef: {
    current: []
  }
});
function FloatingList(props) {
  const {
    children,
    elementsRef,
    labelsRef
  } = props;
  const [map, setMap] = React3.useState(() => /* @__PURE__ */ new Map());
  const register = React3.useCallback((node) => {
    setMap((prevMap) => new Map(prevMap).set(node, null));
  }, []);
  const unregister = React3.useCallback((node) => {
    setMap((prevMap) => {
      const map2 = new Map(prevMap);
      map2.delete(node);
      return map2;
    });
  }, []);
  index2(() => {
    const newMap = new Map(map);
    const nodes = Array.from(newMap.keys()).sort(sortByDocumentPosition);
    nodes.forEach((node, index3) => {
      newMap.set(node, index3);
    });
    if (!areMapsEqual(map, newMap)) {
      setMap(newMap);
    }
  }, [map]);
  return React3.createElement(FloatingListContext.Provider, {
    value: React3.useMemo(() => ({
      register,
      unregister,
      map,
      elementsRef,
      labelsRef
    }), [register, unregister, map, elementsRef, labelsRef])
  }, children);
}
function useListItem(props) {
  if (props === void 0) {
    props = {};
  }
  const {
    label
  } = props;
  const {
    register,
    unregister,
    map,
    elementsRef,
    labelsRef
  } = React3.useContext(FloatingListContext);
  const [index$1, setIndex] = React3.useState(null);
  const componentRef = React3.useRef(null);
  const ref = React3.useCallback((node) => {
    componentRef.current = node;
    if (index$1 !== null) {
      elementsRef.current[index$1] = node;
      if (labelsRef) {
        var _node$textContent;
        const isLabelDefined = label !== void 0;
        labelsRef.current[index$1] = isLabelDefined ? label : (_node$textContent = node == null ? void 0 : node.textContent) != null ? _node$textContent : null;
      }
    }
  }, [index$1, elementsRef, labelsRef, label]);
  index2(() => {
    const node = componentRef.current;
    if (node) {
      register(node);
      return () => {
        unregister(node);
      };
    }
  }, [register, unregister]);
  index2(() => {
    const index3 = componentRef.current ? map.get(componentRef.current) : null;
    if (index3 != null) {
      setIndex(index3);
    }
  }, [map]);
  return React3.useMemo(() => ({
    ref,
    index: index$1 == null ? -1 : index$1
  }), [index$1, ref]);
}
function renderJsx(render, computedProps) {
  if (typeof render === "function") {
    return render(computedProps);
  }
  if (render) {
    return React3.cloneElement(render, computedProps);
  }
  return React3.createElement("div", computedProps);
}
var CompositeContext = React3.createContext({
  activeIndex: 0,
  onNavigate: () => {
  }
});
var horizontalKeys = [ARROW_LEFT, ARROW_RIGHT];
var verticalKeys = [ARROW_UP, ARROW_DOWN];
var allKeys = [...horizontalKeys, ...verticalKeys];
var Composite = React3.forwardRef(function Composite2(props, forwardedRef) {
  const {
    render,
    orientation = "both",
    loop = true,
    rtl = false,
    cols = 1,
    disabledIndices,
    activeIndex: externalActiveIndex,
    onNavigate: externalSetActiveIndex,
    itemSizes,
    dense = false,
    ...domProps
  } = props;
  const [internalActiveIndex, internalSetActiveIndex] = React3.useState(0);
  const activeIndex = externalActiveIndex != null ? externalActiveIndex : internalActiveIndex;
  const onNavigate = useEffectEvent(externalSetActiveIndex != null ? externalSetActiveIndex : internalSetActiveIndex);
  const elementsRef = React3.useRef([]);
  const renderElementProps = render && typeof render !== "function" ? render.props : {};
  const contextValue = React3.useMemo(() => ({
    activeIndex,
    onNavigate
  }), [activeIndex, onNavigate]);
  const isGrid = cols > 1;
  function handleKeyDown(event) {
    if (!allKeys.includes(event.key)) return;
    let nextIndex = activeIndex;
    const minIndex = getMinIndex(elementsRef, disabledIndices);
    const maxIndex = getMaxIndex(elementsRef, disabledIndices);
    const horizontalEndKey = rtl ? ARROW_LEFT : ARROW_RIGHT;
    const horizontalStartKey = rtl ? ARROW_RIGHT : ARROW_LEFT;
    if (isGrid) {
      const sizes = itemSizes || Array.from({
        length: elementsRef.current.length
      }, () => ({
        width: 1,
        height: 1
      }));
      const cellMap = buildCellMap(sizes, cols, dense);
      const minGridIndex = cellMap.findIndex((index3) => index3 != null && !isDisabled(elementsRef.current, index3, disabledIndices));
      const maxGridIndex = cellMap.reduce((foundIndex, index3, cellIndex) => index3 != null && !isDisabled(elementsRef.current, index3, disabledIndices) ? cellIndex : foundIndex, -1);
      const maybeNextIndex = cellMap[getGridNavigatedIndex({
        current: cellMap.map((itemIndex) => itemIndex ? elementsRef.current[itemIndex] : null)
      }, {
        event,
        orientation,
        loop,
        rtl,
        cols,
        // treat undefined (empty grid spaces) as disabled indices so we
        // don't end up in them
        disabledIndices: getCellIndices([...disabledIndices || elementsRef.current.map((_8, index3) => isDisabled(elementsRef.current, index3) ? index3 : void 0), void 0], cellMap),
        minIndex: minGridIndex,
        maxIndex: maxGridIndex,
        prevIndex: getCellIndexOfCorner(
          activeIndex > maxIndex ? minIndex : activeIndex,
          sizes,
          cellMap,
          cols,
          // use a corner matching the edge closest to the direction we're
          // moving in so we don't end up in the same item. Prefer
          // top/left over bottom/right.
          event.key === ARROW_DOWN ? "bl" : event.key === horizontalEndKey ? "tr" : "tl"
        )
      })];
      if (maybeNextIndex != null) {
        nextIndex = maybeNextIndex;
      }
    }
    const toEndKeys = {
      horizontal: [horizontalEndKey],
      vertical: [ARROW_DOWN],
      both: [horizontalEndKey, ARROW_DOWN]
    }[orientation];
    const toStartKeys = {
      horizontal: [horizontalStartKey],
      vertical: [ARROW_UP],
      both: [horizontalStartKey, ARROW_UP]
    }[orientation];
    const preventedKeys = isGrid ? allKeys : {
      horizontal: horizontalKeys,
      vertical: verticalKeys,
      both: allKeys
    }[orientation];
    if (nextIndex === activeIndex && [...toEndKeys, ...toStartKeys].includes(event.key)) {
      if (loop && nextIndex === maxIndex && toEndKeys.includes(event.key)) {
        nextIndex = minIndex;
      } else if (loop && nextIndex === minIndex && toStartKeys.includes(event.key)) {
        nextIndex = maxIndex;
      } else {
        nextIndex = findNonDisabledIndex(elementsRef, {
          startingIndex: nextIndex,
          decrement: toStartKeys.includes(event.key),
          disabledIndices
        });
      }
    }
    if (nextIndex !== activeIndex && !isIndexOutOfBounds(elementsRef, nextIndex)) {
      var _elementsRef$current$;
      event.stopPropagation();
      if (preventedKeys.includes(event.key)) {
        event.preventDefault();
      }
      onNavigate(nextIndex);
      (_elementsRef$current$ = elementsRef.current[nextIndex]) == null || _elementsRef$current$.focus();
    }
  }
  const computedProps = {
    ...domProps,
    ...renderElementProps,
    ref: forwardedRef,
    "aria-orientation": orientation === "both" ? void 0 : orientation,
    onKeyDown(e8) {
      domProps.onKeyDown == null || domProps.onKeyDown(e8);
      renderElementProps.onKeyDown == null || renderElementProps.onKeyDown(e8);
      handleKeyDown(e8);
    }
  };
  return React3.createElement(CompositeContext.Provider, {
    value: contextValue
  }, React3.createElement(FloatingList, {
    elementsRef
  }, renderJsx(render, computedProps)));
});
var CompositeItem = React3.forwardRef(function CompositeItem2(props, forwardedRef) {
  const {
    render,
    ...domProps
  } = props;
  const renderElementProps = render && typeof render !== "function" ? render.props : {};
  const {
    activeIndex,
    onNavigate
  } = React3.useContext(CompositeContext);
  const {
    ref,
    index: index3
  } = useListItem();
  const mergedRef = useMergeRefs([ref, forwardedRef, renderElementProps.ref]);
  const isActive = activeIndex === index3;
  const computedProps = {
    ...domProps,
    ...renderElementProps,
    ref: mergedRef,
    tabIndex: isActive ? 0 : -1,
    "data-active": isActive ? "" : void 0,
    onFocus(e8) {
      domProps.onFocus == null || domProps.onFocus(e8);
      renderElementProps.onFocus == null || renderElementProps.onFocus(e8);
      onNavigate(index3);
    }
  };
  return renderJsx(render, computedProps);
});
function _extends() {
  _extends = Object.assign ? Object.assign.bind() : function(target) {
    for (var i19 = 1; i19 < arguments.length; i19++) {
      var source = arguments[i19];
      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }
    return target;
  };
  return _extends.apply(this, arguments);
}
var serverHandoffComplete = false;
var count = 0;
var genId = () => (
  // Ensure the id is unique with multiple independent versions of Floating UI
  // on <React 18
  "floating-ui-" + Math.random().toString(36).slice(2, 6) + count++
);
function useFloatingId() {
  const [id, setId] = React3.useState(() => serverHandoffComplete ? genId() : void 0);
  index2(() => {
    if (id == null) {
      setId(genId());
    }
  }, []);
  React3.useEffect(() => {
    serverHandoffComplete = true;
  }, []);
  return id;
}
var useReactId = SafeReact.useId;
var useId = useReactId || useFloatingId;
var devMessageSet;
if (true) {
  devMessageSet = /* @__PURE__ */ new Set();
}
function warn() {
  var _devMessageSet;
  for (var _len = arguments.length, messages = new Array(_len), _key = 0; _key < _len; _key++) {
    messages[_key] = arguments[_key];
  }
  const message = "Floating UI: " + messages.join(" ");
  if (!((_devMessageSet = devMessageSet) != null && _devMessageSet.has(message))) {
    var _devMessageSet2;
    (_devMessageSet2 = devMessageSet) == null || _devMessageSet2.add(message);
    console.warn(message);
  }
}
function error() {
  var _devMessageSet3;
  for (var _len2 = arguments.length, messages = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
    messages[_key2] = arguments[_key2];
  }
  const message = "Floating UI: " + messages.join(" ");
  if (!((_devMessageSet3 = devMessageSet) != null && _devMessageSet3.has(message))) {
    var _devMessageSet4;
    (_devMessageSet4 = devMessageSet) == null || _devMessageSet4.add(message);
    console.error(message);
  }
}
var FloatingArrow = React3.forwardRef(function FloatingArrow2(props, ref) {
  const {
    context: {
      placement,
      elements: {
        floating
      },
      middlewareData: {
        arrow: arrow4,
        shift: shift4
      }
    },
    width = 14,
    height = 7,
    tipRadius = 0,
    strokeWidth = 0,
    staticOffset,
    stroke,
    d: d14,
    style: {
      transform,
      ...restStyle
    } = {},
    ...rest
  } = props;
  if (true) {
    if (!ref) {
      warn("The `ref` prop is required for `FloatingArrow`.");
    }
  }
  const clipPathId = useId();
  const [isRTL2, setIsRTL] = React3.useState(false);
  index2(() => {
    if (!floating) return;
    const isRTL3 = getComputedStyle2(floating).direction === "rtl";
    if (isRTL3) {
      setIsRTL(true);
    }
  }, [floating]);
  if (!floating) {
    return null;
  }
  const [side, alignment] = placement.split("-");
  const isVerticalSide = side === "top" || side === "bottom";
  let computedStaticOffset = staticOffset;
  if (isVerticalSide && shift4 != null && shift4.x || !isVerticalSide && shift4 != null && shift4.y) {
    computedStaticOffset = null;
  }
  const computedStrokeWidth = strokeWidth * 2;
  const halfStrokeWidth = computedStrokeWidth / 2;
  const svgX = width / 2 * (tipRadius / -8 + 1);
  const svgY = height / 2 * tipRadius / 4;
  const isCustomShape = !!d14;
  const yOffsetProp = computedStaticOffset && alignment === "end" ? "bottom" : "top";
  let xOffsetProp = computedStaticOffset && alignment === "end" ? "right" : "left";
  if (computedStaticOffset && isRTL2) {
    xOffsetProp = alignment === "end" ? "left" : "right";
  }
  const arrowX = (arrow4 == null ? void 0 : arrow4.x) != null ? computedStaticOffset || arrow4.x : "";
  const arrowY = (arrow4 == null ? void 0 : arrow4.y) != null ? computedStaticOffset || arrow4.y : "";
  const dValue = d14 || "M0,0" + (" H" + width) + (" L" + (width - svgX) + "," + (height - svgY)) + (" Q" + width / 2 + "," + height + " " + svgX + "," + (height - svgY)) + " Z";
  const rotation = {
    top: isCustomShape ? "rotate(180deg)" : "",
    left: isCustomShape ? "rotate(90deg)" : "rotate(-90deg)",
    bottom: isCustomShape ? "" : "rotate(180deg)",
    right: isCustomShape ? "rotate(-90deg)" : "rotate(90deg)"
  }[side];
  return React3.createElement("svg", _extends({}, rest, {
    "aria-hidden": true,
    ref,
    width: isCustomShape ? width : width + computedStrokeWidth,
    height: width,
    viewBox: "0 0 " + width + " " + (height > width ? height : width),
    style: {
      position: "absolute",
      pointerEvents: "none",
      [xOffsetProp]: arrowX,
      [yOffsetProp]: arrowY,
      [side]: isVerticalSide || isCustomShape ? "100%" : "calc(100% - " + computedStrokeWidth / 2 + "px)",
      transform: [rotation, transform].filter((t12) => !!t12).join(" "),
      ...restStyle
    }
  }), computedStrokeWidth > 0 && React3.createElement("path", {
    clipPath: "url(#" + clipPathId + ")",
    fill: "none",
    stroke,
    strokeWidth: computedStrokeWidth + (d14 ? 0 : 1),
    d: dValue
  }), React3.createElement("path", {
    stroke: computedStrokeWidth && !d14 ? rest.fill : "none",
    d: dValue
  }), React3.createElement("clipPath", {
    id: clipPathId
  }, React3.createElement("rect", {
    x: -halfStrokeWidth,
    y: halfStrokeWidth * (isCustomShape ? -1 : 1),
    width: width + computedStrokeWidth,
    height: width
  })));
});
function createPubSub() {
  const map = /* @__PURE__ */ new Map();
  return {
    emit(event, data) {
      var _map$get;
      (_map$get = map.get(event)) == null || _map$get.forEach((handler) => handler(data));
    },
    on(event, listener) {
      map.set(event, [...map.get(event) || [], listener]);
    },
    off(event, listener) {
      var _map$get2;
      map.set(event, ((_map$get2 = map.get(event)) == null ? void 0 : _map$get2.filter((l17) => l17 !== listener)) || []);
    }
  };
}
var FloatingNodeContext = React3.createContext(null);
var FloatingTreeContext = React3.createContext(null);
var useFloatingParentNodeId = () => {
  var _React$useContext;
  return ((_React$useContext = React3.useContext(FloatingNodeContext)) == null ? void 0 : _React$useContext.id) || null;
};
var useFloatingTree = () => React3.useContext(FloatingTreeContext);
function createAttribute(name) {
  return "data-floating-ui-" + name;
}
var safePolygonIdentifier = createAttribute("safe-polygon");
var NOOP = () => {
};
var FloatingDelayGroupContext = React3.createContext({
  delay: 0,
  initialDelay: 0,
  timeoutMs: 0,
  currentId: null,
  setCurrentId: NOOP,
  setState: NOOP,
  isInstantPhase: false
});
var HIDDEN_STYLES = {
  border: 0,
  clip: "rect(0 0 0 0)",
  height: "1px",
  margin: "-1px",
  overflow: "hidden",
  padding: 0,
  position: "fixed",
  whiteSpace: "nowrap",
  width: "1px",
  top: 0,
  left: 0
};
var timeoutId;
function setActiveElementOnTab(event) {
  if (event.key === "Tab") {
    event.target;
    clearTimeout(timeoutId);
  }
}
var FocusGuard = React3.forwardRef(function FocusGuard2(props, ref) {
  const [role, setRole] = React3.useState();
  index2(() => {
    if (isSafari()) {
      setRole("button");
    }
    document.addEventListener("keydown", setActiveElementOnTab);
    return () => {
      document.removeEventListener("keydown", setActiveElementOnTab);
    };
  }, []);
  const restProps = {
    ref,
    tabIndex: 0,
    // Role is only for VoiceOver
    role,
    "aria-hidden": role ? void 0 : true,
    [createAttribute("focus-guard")]: "",
    style: HIDDEN_STYLES
  };
  return React3.createElement("span", _extends({}, props, restProps));
});
var PortalContext = React3.createContext(null);
var attr = createAttribute("portal");
var FOCUSABLE_ATTRIBUTE = "data-floating-ui-focusable";
var VisuallyHiddenDismiss = React3.forwardRef(function VisuallyHiddenDismiss2(props, ref) {
  return React3.createElement("button", _extends({}, props, {
    type: "button",
    ref,
    tabIndex: -1,
    style: HIDDEN_STYLES
  }));
});
var lockCount = 0;
function enableScrollLock() {
  const isIOS = /iP(hone|ad|od)|iOS/.test(getPlatform());
  const bodyStyle = document.body.style;
  const scrollbarX = Math.round(document.documentElement.getBoundingClientRect().left) + document.documentElement.scrollLeft;
  const paddingProp = scrollbarX ? "paddingLeft" : "paddingRight";
  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
  const scrollX = bodyStyle.left ? parseFloat(bodyStyle.left) : window.scrollX;
  const scrollY = bodyStyle.top ? parseFloat(bodyStyle.top) : window.scrollY;
  bodyStyle.overflow = "hidden";
  if (scrollbarWidth) {
    bodyStyle[paddingProp] = scrollbarWidth + "px";
  }
  if (isIOS) {
    var _window$visualViewpor, _window$visualViewpor2;
    const offsetLeft = ((_window$visualViewpor = window.visualViewport) == null ? void 0 : _window$visualViewpor.offsetLeft) || 0;
    const offsetTop = ((_window$visualViewpor2 = window.visualViewport) == null ? void 0 : _window$visualViewpor2.offsetTop) || 0;
    Object.assign(bodyStyle, {
      position: "fixed",
      top: -(scrollY - Math.floor(offsetTop)) + "px",
      left: -(scrollX - Math.floor(offsetLeft)) + "px",
      right: "0"
    });
  }
  return () => {
    Object.assign(bodyStyle, {
      overflow: "",
      [paddingProp]: ""
    });
    if (isIOS) {
      Object.assign(bodyStyle, {
        position: "",
        top: "",
        left: "",
        right: ""
      });
      window.scrollTo(scrollX, scrollY);
    }
  };
}
var cleanup = () => {
};
var FloatingOverlay = React3.forwardRef(function FloatingOverlay2(props, ref) {
  const {
    lockScroll = false,
    ...rest
  } = props;
  index2(() => {
    if (!lockScroll) return;
    lockCount++;
    if (lockCount === 1) {
      cleanup = enableScrollLock();
    }
    return () => {
      lockCount--;
      if (lockCount === 0) {
        cleanup();
      }
    };
  }, [lockScroll]);
  return React3.createElement("div", _extends({
    ref
  }, rest, {
    style: {
      position: "fixed",
      overflow: "auto",
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      ...rest.style
    }
  }));
});
function useFloatingRootContext(options) {
  const {
    open = false,
    onOpenChange: onOpenChangeProp,
    elements: elementsProp
  } = options;
  const floatingId = useId();
  const dataRef = React3.useRef({});
  const [events] = React3.useState(() => createPubSub());
  const nested = useFloatingParentNodeId() != null;
  if (true) {
    const optionDomReference = elementsProp.reference;
    if (optionDomReference && !isElement(optionDomReference)) {
      error("Cannot pass a virtual element to the `elements.reference` option,", "as it must be a real DOM element. Use `refs.setPositionReference()`", "instead.");
    }
  }
  const [positionReference, setPositionReference] = React3.useState(elementsProp.reference);
  const onOpenChange = useEffectEvent((open2, event, reason) => {
    dataRef.current.openEvent = open2 ? event : void 0;
    events.emit("openchange", {
      open: open2,
      event,
      reason,
      nested
    });
    onOpenChangeProp == null || onOpenChangeProp(open2, event, reason);
  });
  const refs = React3.useMemo(() => ({
    setPositionReference
  }), []);
  const elements = React3.useMemo(() => ({
    reference: positionReference || elementsProp.reference || null,
    floating: elementsProp.floating || null,
    domReference: elementsProp.reference
  }), [positionReference, elementsProp.reference, elementsProp.floating]);
  return React3.useMemo(() => ({
    dataRef,
    open,
    onOpenChange,
    elements,
    events,
    floatingId,
    refs
  }), [open, onOpenChange, elements, events, floatingId, refs]);
}
function useFloating2(options) {
  if (options === void 0) {
    options = {};
  }
  const {
    nodeId
  } = options;
  const internalRootContext = useFloatingRootContext({
    ...options,
    elements: {
      reference: null,
      floating: null,
      ...options.elements
    }
  });
  const rootContext = options.rootContext || internalRootContext;
  const computedElements = rootContext.elements;
  const [_domReference, setDomReference] = React3.useState(null);
  const [positionReference, _setPositionReference] = React3.useState(null);
  const optionDomReference = computedElements == null ? void 0 : computedElements.domReference;
  const domReference = optionDomReference || _domReference;
  const domReferenceRef = React3.useRef(null);
  const tree = useFloatingTree();
  index2(() => {
    if (domReference) {
      domReferenceRef.current = domReference;
    }
  }, [domReference]);
  const position = useFloating({
    ...options,
    elements: {
      ...computedElements,
      ...positionReference && {
        reference: positionReference
      }
    }
  });
  const setPositionReference = React3.useCallback((node) => {
    const computedPositionReference = isElement(node) ? {
      getBoundingClientRect: () => node.getBoundingClientRect(),
      contextElement: node
    } : node;
    _setPositionReference(computedPositionReference);
    position.refs.setReference(computedPositionReference);
  }, [position.refs]);
  const setReference = React3.useCallback((node) => {
    if (isElement(node) || node === null) {
      domReferenceRef.current = node;
      setDomReference(node);
    }
    if (isElement(position.refs.reference.current) || position.refs.reference.current === null || // Don't allow setting virtual elements using the old technique back to
    // `null` to support `positionReference` + an unstable `reference`
    // callback ref.
    node !== null && !isElement(node)) {
      position.refs.setReference(node);
    }
  }, [position.refs]);
  const refs = React3.useMemo(() => ({
    ...position.refs,
    setReference,
    setPositionReference,
    domReference: domReferenceRef
  }), [position.refs, setReference, setPositionReference]);
  const elements = React3.useMemo(() => ({
    ...position.elements,
    domReference
  }), [position.elements, domReference]);
  const context = React3.useMemo(() => ({
    ...position,
    ...rootContext,
    refs,
    elements,
    nodeId
  }), [position, refs, elements, nodeId, rootContext]);
  index2(() => {
    rootContext.dataRef.current.floatingContext = context;
    const node = tree == null ? void 0 : tree.nodesRef.current.find((node2) => node2.id === nodeId);
    if (node) {
      node.context = context;
    }
  });
  return React3.useMemo(() => ({
    ...position,
    context,
    refs,
    elements
  }), [position, refs, elements, context]);
}
var ACTIVE_KEY = "active";
var SELECTED_KEY = "selected";
function mergeProps(userProps, propsList, elementKey) {
  const map = /* @__PURE__ */ new Map();
  const isItem = elementKey === "item";
  let domUserProps = userProps;
  if (isItem && userProps) {
    const {
      [ACTIVE_KEY]: _8,
      [SELECTED_KEY]: __,
      ...validProps
    } = userProps;
    domUserProps = validProps;
  }
  return {
    ...elementKey === "floating" && {
      tabIndex: -1,
      [FOCUSABLE_ATTRIBUTE]: ""
    },
    ...domUserProps,
    ...propsList.map((value) => {
      const propsOrGetProps = value ? value[elementKey] : null;
      if (typeof propsOrGetProps === "function") {
        return userProps ? propsOrGetProps(userProps) : null;
      }
      return propsOrGetProps;
    }).concat(userProps).reduce((acc, props) => {
      if (!props) {
        return acc;
      }
      Object.entries(props).forEach((_ref) => {
        let [key, value] = _ref;
        if (isItem && [ACTIVE_KEY, SELECTED_KEY].includes(key)) {
          return;
        }
        if (key.indexOf("on") === 0) {
          if (!map.has(key)) {
            map.set(key, []);
          }
          if (typeof value === "function") {
            var _map$get;
            (_map$get = map.get(key)) == null || _map$get.push(value);
            acc[key] = function() {
              var _map$get2;
              for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
                args[_key] = arguments[_key];
              }
              return (_map$get2 = map.get(key)) == null ? void 0 : _map$get2.map((fn) => fn(...args)).find((val) => val !== void 0);
            };
          }
        } else {
          acc[key] = value;
        }
      });
      return acc;
    }, {})
  };
}
function useInteractions(propsList) {
  if (propsList === void 0) {
    propsList = [];
  }
  const referenceDeps = propsList.map((key) => key == null ? void 0 : key.reference);
  const floatingDeps = propsList.map((key) => key == null ? void 0 : key.floating);
  const itemDeps = propsList.map((key) => key == null ? void 0 : key.item);
  const getReferenceProps = React3.useCallback(
    (userProps) => mergeProps(userProps, propsList, "reference"),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    referenceDeps
  );
  const getFloatingProps = React3.useCallback(
    (userProps) => mergeProps(userProps, propsList, "floating"),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    floatingDeps
  );
  const getItemProps = React3.useCallback(
    (userProps) => mergeProps(userProps, propsList, "item"),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    itemDeps
  );
  return React3.useMemo(() => ({
    getReferenceProps,
    getFloatingProps,
    getItemProps
  }), [getReferenceProps, getFloatingProps, getItemProps]);
}
function getArgsWithCustomFloatingHeight(state, height) {
  return {
    ...state,
    rects: {
      ...state.rects,
      floating: {
        ...state.rects.floating,
        height
      }
    }
  };
}
var inner = (props) => ({
  name: "inner",
  options: props,
  async fn(state) {
    const {
      listRef,
      overflowRef,
      onFallbackChange,
      offset: innerOffset = 0,
      index: index3 = 0,
      minItemsVisible = 4,
      referenceOverflowThreshold = 0,
      scrollRef,
      ...detectOverflowOptions
    } = evaluate(props, state);
    const {
      rects,
      elements: {
        floating
      }
    } = state;
    const item = listRef.current[index3];
    const scrollEl = (scrollRef == null ? void 0 : scrollRef.current) || floating;
    const clientTop = floating.clientTop || scrollEl.clientTop;
    const floatingIsBordered = floating.clientTop !== 0;
    const scrollElIsBordered = scrollEl.clientTop !== 0;
    const floatingIsScrollEl = floating === scrollEl;
    if (true) {
      if (!state.placement.startsWith("bottom")) {
        warn('`placement` side must be "bottom" when using the `inner`', "middleware.");
      }
    }
    if (!item) {
      return {};
    }
    const nextArgs = {
      ...state,
      ...await offset3(-item.offsetTop - floating.clientTop - rects.reference.height / 2 - item.offsetHeight / 2 - innerOffset).fn(state)
    };
    const overflow = await detectOverflow2(getArgsWithCustomFloatingHeight(nextArgs, scrollEl.scrollHeight + clientTop + floating.clientTop), detectOverflowOptions);
    const refOverflow = await detectOverflow2(nextArgs, {
      ...detectOverflowOptions,
      elementContext: "reference"
    });
    const diffY = max(0, overflow.top);
    const nextY = nextArgs.y + diffY;
    const isScrollable = scrollEl.scrollHeight > scrollEl.clientHeight;
    const rounder = isScrollable ? (v5) => v5 : round;
    const maxHeight = rounder(max(0, scrollEl.scrollHeight + (floatingIsBordered && floatingIsScrollEl || scrollElIsBordered ? clientTop * 2 : 0) - diffY - max(0, overflow.bottom)));
    scrollEl.style.maxHeight = maxHeight + "px";
    scrollEl.scrollTop = diffY;
    if (onFallbackChange) {
      const shouldFallback = scrollEl.offsetHeight < item.offsetHeight * min(minItemsVisible, listRef.current.length) - 1 || refOverflow.top >= -referenceOverflowThreshold || refOverflow.bottom >= -referenceOverflowThreshold;
      ReactDOM2.flushSync(() => onFallbackChange(shouldFallback));
    }
    if (overflowRef) {
      overflowRef.current = await detectOverflow2(getArgsWithCustomFloatingHeight({
        ...nextArgs,
        y: nextY
      }, scrollEl.offsetHeight + clientTop + floating.clientTop), detectOverflowOptions);
    }
    return {
      y: nextY
    };
  }
});
function useInnerOffset(context, props) {
  const {
    open,
    elements
  } = context;
  const {
    enabled = true,
    overflowRef,
    scrollRef,
    onChange: unstable_onChange
  } = props;
  const onChange = useEffectEvent(unstable_onChange);
  const controlledScrollingRef = React3.useRef(false);
  const prevScrollTopRef = React3.useRef(null);
  const initialOverflowRef = React3.useRef(null);
  React3.useEffect(() => {
    if (!enabled) return;
    function onWheel(e8) {
      if (e8.ctrlKey || !el || overflowRef.current == null) {
        return;
      }
      const dY = e8.deltaY;
      const isAtTop = overflowRef.current.top >= -0.5;
      const isAtBottom = overflowRef.current.bottom >= -0.5;
      const remainingScroll = el.scrollHeight - el.clientHeight;
      const sign = dY < 0 ? -1 : 1;
      const method = dY < 0 ? "max" : "min";
      if (el.scrollHeight <= el.clientHeight) {
        return;
      }
      if (!isAtTop && dY > 0 || !isAtBottom && dY < 0) {
        e8.preventDefault();
        ReactDOM2.flushSync(() => {
          onChange((d14) => d14 + Math[method](dY, remainingScroll * sign));
        });
      } else if (/firefox/i.test(getUserAgent())) {
        el.scrollTop += dY;
      }
    }
    const el = (scrollRef == null ? void 0 : scrollRef.current) || elements.floating;
    if (open && el) {
      el.addEventListener("wheel", onWheel);
      requestAnimationFrame(() => {
        prevScrollTopRef.current = el.scrollTop;
        if (overflowRef.current != null) {
          initialOverflowRef.current = {
            ...overflowRef.current
          };
        }
      });
      return () => {
        prevScrollTopRef.current = null;
        initialOverflowRef.current = null;
        el.removeEventListener("wheel", onWheel);
      };
    }
  }, [enabled, open, elements.floating, overflowRef, scrollRef, onChange]);
  const floating = React3.useMemo(() => ({
    onKeyDown() {
      controlledScrollingRef.current = true;
    },
    onWheel() {
      controlledScrollingRef.current = false;
    },
    onPointerMove() {
      controlledScrollingRef.current = false;
    },
    onScroll() {
      const el = (scrollRef == null ? void 0 : scrollRef.current) || elements.floating;
      if (!overflowRef.current || !el || !controlledScrollingRef.current) {
        return;
      }
      if (prevScrollTopRef.current !== null) {
        const scrollDiff = el.scrollTop - prevScrollTopRef.current;
        if (overflowRef.current.bottom < -0.5 && scrollDiff < -1 || overflowRef.current.top < -0.5 && scrollDiff > 1) {
          ReactDOM2.flushSync(() => onChange((d14) => d14 + scrollDiff));
        }
      }
      requestAnimationFrame(() => {
        prevScrollTopRef.current = el.scrollTop;
      });
    }
  }), [elements.floating, onChange, overflowRef, scrollRef]);
  return React3.useMemo(() => enabled ? {
    floating
  } : {}, [enabled, floating]);
}

// node_modules/@headlessui/react/dist/internal/floating.js
var j5 = __toESM(require_react(), 1);
var import_react79 = __toESM(require_react(), 1);
var y5 = (0, import_react79.createContext)({ styles: void 0, setReference: () => {
}, setFloating: () => {
}, getReferenceProps: () => ({}), getFloatingProps: () => ({}), slot: {} });
y5.displayName = "FloatingContext";
var H6 = (0, import_react79.createContext)(null);
H6.displayName = "PlacementContext";
function xe(e8) {
  return (0, import_react79.useMemo)(() => e8 ? typeof e8 == "string" ? { to: e8 } : e8 : null, [e8]);
}
function ye() {
  return (0, import_react79.useContext)(y5).setReference;
}
function Fe2() {
  return (0, import_react79.useContext)(y5).getReferenceProps;
}
function be() {
  let { getFloatingProps: e8, slot: t12 } = (0, import_react79.useContext)(y5);
  return (0, import_react79.useCallback)((...n13) => Object.assign({}, e8(...n13), { "data-anchor": t12.anchor }), [e8, t12]);
}
function Re(e8 = null) {
  e8 === false && (e8 = null), typeof e8 == "string" && (e8 = { to: e8 });
  let t12 = (0, import_react79.useContext)(H6), n13 = (0, import_react79.useMemo)(() => e8, [JSON.stringify(e8, (r20, o21) => {
    var u19;
    return (u19 = o21 == null ? void 0 : o21.outerHTML) != null ? u19 : o21;
  })]);
  n(() => {
    t12 == null || t12(n13 != null ? n13 : null);
  }, [t12, n13]);
  let l17 = (0, import_react79.useContext)(y5);
  return (0, import_react79.useMemo)(() => [l17.setFloating, e8 ? l17.styles : {}], [l17.setFloating, e8, l17.styles]);
}
var q = 4;
function Me({ children: e8, enabled: t12 = true }) {
  let [n13, l17] = (0, import_react79.useState)(null), [r20, o21] = (0, import_react79.useState)(0), u19 = (0, import_react79.useRef)(null), [f22, s15] = (0, import_react79.useState)(null);
  pe2(f22);
  let i19 = t12 && n13 !== null && f22 !== null, { to: F9 = "bottom", gap: E15 = 0, offset: v5 = 0, padding: c19 = 0, inner: P7 } = ce(n13, f22), [a25, p6 = "center"] = F9.split(" ");
  n(() => {
    i19 && o21(0);
  }, [i19]);
  let { refs: b11, floatingStyles: w10, context: g8 } = useFloating2({ open: i19, placement: a25 === "selection" ? p6 === "center" ? "bottom" : `bottom-${p6}` : p6 === "center" ? `${a25}` : `${a25}-${p6}`, strategy: "absolute", transform: false, middleware: [offset3({ mainAxis: a25 === "selection" ? 0 : E15, crossAxis: v5 }), shift3({ padding: c19 }), a25 !== "selection" && flip3({ padding: c19 }), a25 === "selection" && P7 ? inner({ ...P7, padding: c19, overflowRef: u19, offset: r20, minItemsVisible: q, referenceOverflowThreshold: c19, onFallbackChange(h11) {
    var O9, W3;
    if (!h11) return;
    let d14 = g8.elements.floating;
    if (!d14) return;
    let T12 = parseFloat(getComputedStyle(d14).scrollPaddingBottom) || 0, $4 = Math.min(q, d14.childElementCount), L7 = 0, N3 = 0;
    for (let m11 of (W3 = (O9 = g8.elements.floating) == null ? void 0 : O9.childNodes) != null ? W3 : []) if (m11 instanceof HTMLElement) {
      let x11 = m11.offsetTop, k5 = x11 + m11.clientHeight + T12, S10 = d14.scrollTop, U7 = S10 + d14.clientHeight;
      if (x11 >= S10 && k5 <= U7) $4--;
      else {
        N3 = Math.max(0, Math.min(k5, U7) - Math.max(x11, S10)), L7 = m11.clientHeight;
        break;
      }
    }
    $4 >= 1 && o21((m11) => {
      let x11 = L7 * $4 - N3 + T12;
      return m11 >= x11 ? m11 : x11;
    });
  } }) : null, size3({ padding: c19, apply({ availableWidth: h11, availableHeight: d14, elements: T12 }) {
    Object.assign(T12.floating.style, { overflow: "auto", maxWidth: `${h11}px`, maxHeight: `min(var(--anchor-max-height, 100vh), ${d14}px)` });
  } })].filter(Boolean), whileElementsMounted: autoUpdate }), [I7 = a25, B5 = p6] = g8.placement.split("-");
  a25 === "selection" && (I7 = "selection");
  let G8 = (0, import_react79.useMemo)(() => ({ anchor: [I7, B5].filter(Boolean).join(" ") }), [I7, B5]), K4 = useInnerOffset(g8, { overflowRef: u19, onChange: o21 }), { getReferenceProps: Q4, getFloatingProps: X6 } = useInteractions([K4]), Y4 = o5((h11) => {
    s15(h11), b11.setFloating(h11);
  });
  return j5.createElement(H6.Provider, { value: l17 }, j5.createElement(y5.Provider, { value: { setFloating: Y4, setReference: b11.setReference, styles: w10, getReferenceProps: Q4, getFloatingProps: X6, slot: G8 } }, e8));
}
function pe2(e8) {
  n(() => {
    if (!e8) return;
    let t12 = new MutationObserver(() => {
      let n13 = window.getComputedStyle(e8).maxHeight, l17 = parseFloat(n13);
      if (isNaN(l17)) return;
      let r20 = parseInt(n13);
      isNaN(r20) || l17 !== r20 && (e8.style.maxHeight = `${Math.ceil(l17)}px`);
    });
    return t12.observe(e8, { attributes: true, attributeFilter: ["style"] }), () => {
      t12.disconnect();
    };
  }, [e8]);
}
function ce(e8, t12) {
  var o21, u19, f22;
  let n13 = V((o21 = e8 == null ? void 0 : e8.gap) != null ? o21 : "var(--anchor-gap, 0)", t12), l17 = V((u19 = e8 == null ? void 0 : e8.offset) != null ? u19 : "var(--anchor-offset, 0)", t12), r20 = V((f22 = e8 == null ? void 0 : e8.padding) != null ? f22 : "var(--anchor-padding, 0)", t12);
  return { ...e8, gap: n13, offset: l17, padding: r20 };
}
function V(e8, t12, n13 = void 0) {
  let l17 = p(), r20 = o5((s15, i19) => {
    if (s15 == null) return [n13, null];
    if (typeof s15 == "number") return [s15, null];
    if (typeof s15 == "string") {
      if (!i19) return [n13, null];
      let F9 = J2(s15, i19);
      return [F9, (E15) => {
        let v5 = D2(s15);
        {
          let c19 = v5.map((P7) => window.getComputedStyle(i19).getPropertyValue(P7));
          l17.requestAnimationFrame(function P7() {
            l17.nextFrame(P7);
            let a25 = false;
            for (let [b11, w10] of v5.entries()) {
              let g8 = window.getComputedStyle(i19).getPropertyValue(w10);
              if (c19[b11] !== g8) {
                c19[b11] = g8, a25 = true;
                break;
              }
            }
            if (!a25) return;
            let p6 = J2(s15, i19);
            F9 !== p6 && (E15(p6), F9 = p6);
          });
        }
        return l17.dispose;
      }];
    }
    return [n13, null];
  }), o21 = (0, import_react79.useMemo)(() => r20(e8, t12)[0], [e8, t12]), [u19 = o21, f22] = (0, import_react79.useState)();
  return n(() => {
    let [s15, i19] = r20(e8, t12);
    if (f22(s15), !!i19) return i19(f22);
  }, [e8, t12]), u19;
}
function D2(e8) {
  let t12 = /var\((.*)\)/.exec(e8);
  if (t12) {
    let n13 = t12[1].indexOf(",");
    if (n13 === -1) return [t12[1]];
    let l17 = t12[1].slice(0, n13).trim(), r20 = t12[1].slice(n13 + 1).trim();
    return r20 ? [l17, ...D2(r20)] : [l17];
  }
  return [];
}
function J2(e8, t12) {
  let n13 = document.createElement("div");
  t12.appendChild(n13), n13.style.setProperty("margin-top", "0px", "important"), n13.style.setProperty("margin-top", e8, "important");
  let l17 = parseFloat(window.getComputedStyle(n13).marginTop) || 0;
  return t12.removeChild(n13), l17;
}

// node_modules/@headlessui/react/dist/internal/frozen.js
var import_react80 = __toESM(require_react(), 1);
function f13({ children: o21, freeze: e8 }) {
  let n13 = l7(e8, o21);
  return import_react80.default.createElement(import_react80.default.Fragment, null, n13);
}
function l7(o21, e8) {
  let [n13, t12] = (0, import_react80.useState)(e8);
  return !o21 && n13 !== e8 && t12(e8), o21 ? n13 : e8;
}

// node_modules/@headlessui/react/dist/internal/open-closed.js
var import_react81 = __toESM(require_react(), 1);
var n10 = (0, import_react81.createContext)(null);
n10.displayName = "OpenClosedContext";
var i11 = ((e8) => (e8[e8.Open = 1] = "Open", e8[e8.Closed = 2] = "Closed", e8[e8.Closing = 4] = "Closing", e8[e8.Opening = 8] = "Opening", e8))(i11 || {});
function u12() {
  return (0, import_react81.useContext)(n10);
}
function c8({ value: o21, children: t12 }) {
  return import_react81.default.createElement(n10.Provider, { value: o21 }, t12);
}
function s7({ children: o21 }) {
  return import_react81.default.createElement(n10.Provider, { value: null }, o21);
}

// node_modules/@headlessui/react/dist/react-glue.js
var import_with_selector = __toESM(require_with_selector(), 1);

// node_modules/@headlessui/react/dist/machine.js
var f14 = (t12, e8, r20) => {
  if (!e8.has(t12)) throw TypeError("Cannot " + r20);
};
var a12 = (t12, e8, r20) => (f14(t12, e8, "read from private field"), r20 ? r20.call(t12) : e8.get(t12));
var l9 = (t12, e8, r20) => {
  if (e8.has(t12)) throw TypeError("Cannot add the same private member more than once");
  e8 instanceof WeakSet ? e8.add(t12) : e8.set(t12, r20);
};
var c9 = (t12, e8, r20, n13) => (f14(t12, e8, "write to private field"), n13 ? n13.call(t12, r20) : e8.set(t12, r20), r20);
var i12;
var s8;
var o14;
var m9 = class {
  constructor(e8) {
    l9(this, i12, {});
    l9(this, s8, new a6(() => /* @__PURE__ */ new Set()));
    l9(this, o14, /* @__PURE__ */ new Set());
    c9(this, i12, e8);
  }
  get state() {
    return a12(this, i12);
  }
  subscribe(e8, r20) {
    let n13 = { selector: e8, callback: r20, current: e8(a12(this, i12)) };
    return a12(this, o14).add(n13), () => {
      a12(this, o14).delete(n13);
    };
  }
  on(e8, r20) {
    return a12(this, s8).get(e8).add(r20), () => {
      a12(this, s8).get(e8).delete(r20);
    };
  }
  send(e8) {
    c9(this, i12, this.reduce(a12(this, i12), e8));
    for (let r20 of a12(this, o14)) {
      let n13 = r20.selector(a12(this, i12));
      h6(r20.current, n13) || (r20.current = n13, r20.callback(n13));
    }
    for (let r20 of a12(this, s8).get(e8.type)) r20(a12(this, i12), e8);
  }
};
i12 = /* @__PURE__ */ new WeakMap(), s8 = /* @__PURE__ */ new WeakMap(), o14 = /* @__PURE__ */ new WeakMap();
function h6(t12, e8) {
  return Object.is(t12, e8) ? true : typeof t12 != "object" || t12 === null || typeof e8 != "object" || e8 === null ? false : Array.isArray(t12) && Array.isArray(e8) ? t12.length !== e8.length ? false : u13(t12[Symbol.iterator](), e8[Symbol.iterator]()) : t12 instanceof Map && e8 instanceof Map || t12 instanceof Set && e8 instanceof Set ? t12.size !== e8.size ? false : u13(t12.entries(), e8.entries()) : S5(t12) && S5(e8) ? u13(Object.entries(t12)[Symbol.iterator](), Object.entries(e8)[Symbol.iterator]()) : false;
}
function u13(t12, e8) {
  do {
    let r20 = t12.next(), n13 = e8.next();
    if (r20.done && n13.done) return true;
    if (r20.done || n13.done || !Object.is(r20.value, n13.value)) return false;
  } while (true);
}
function S5(t12) {
  if (Object.prototype.toString.call(t12) !== "[object Object]") return false;
  let e8 = Object.getPrototypeOf(t12);
  return e8 === null || Object.getPrototypeOf(e8) === null;
}
function g2(t12) {
  let [e8, r20] = t12(), n13 = o3();
  return (...b11) => {
    e8(...b11), n13.dispose(), n13.microTask(r20);
  };
}

// node_modules/@headlessui/react/dist/react-glue.js
function S6(e8, n13, r20 = h6) {
  return (0, import_with_selector.useSyncExternalStoreWithSelector)(o5((i19) => e8.subscribe(s9, i19)), o5(() => e8.state), o5(() => e8.state), o5(n13), r20);
}
function s9(e8) {
  return e8;
}

// node_modules/@headlessui/react/dist/utils/document-ready.js
function t7(n13) {
  function e8() {
    document.readyState !== "loading" && (n13(), document.removeEventListener("DOMContentLoaded", e8));
  }
  typeof window != "undefined" && typeof document != "undefined" && (document.addEventListener("DOMContentLoaded", e8), e8());
}

// node_modules/@headlessui/react/dist/utils/active-element-history.js
var r11 = [];
t7(() => {
  function e8(t12) {
    if (!(t12.target instanceof HTMLElement) || t12.target === document.body || r11[0] === t12.target) return;
    let n13 = t12.target;
    n13 = n13.closest(f10), r11.unshift(n13 != null ? n13 : t12.target), r11 = r11.filter((o21) => o21 != null && o21.isConnected), r11.splice(10);
  }
  window.addEventListener("click", e8, { capture: true }), window.addEventListener("mousedown", e8, { capture: true }), window.addEventListener("focus", e8, { capture: true }), document.body.addEventListener("click", e8, { capture: true }), document.body.addEventListener("mousedown", e8, { capture: true }), document.body.addEventListener("focus", e8, { capture: true });
});

// node_modules/@headlessui/react/dist/utils/calculate-active-index.js
function u14(l17) {
  throw new Error("Unexpected object: " + l17);
}
var c10 = ((i19) => (i19[i19.First = 0] = "First", i19[i19.Previous = 1] = "Previous", i19[i19.Next = 2] = "Next", i19[i19.Last = 3] = "Last", i19[i19.Specific = 4] = "Specific", i19[i19.Nothing = 5] = "Nothing", i19))(c10 || {});
function f15(l17, n13) {
  let t12 = n13.resolveItems();
  if (t12.length <= 0) return null;
  let r20 = n13.resolveActiveIndex(), s15 = r20 != null ? r20 : -1;
  switch (l17.focus) {
    case 0: {
      for (let e8 = 0; e8 < t12.length; ++e8) if (!n13.resolveDisabled(t12[e8], e8, t12)) return e8;
      return r20;
    }
    case 1: {
      s15 === -1 && (s15 = t12.length);
      for (let e8 = s15 - 1; e8 >= 0; --e8) if (!n13.resolveDisabled(t12[e8], e8, t12)) return e8;
      return r20;
    }
    case 2: {
      for (let e8 = s15 + 1; e8 < t12.length; ++e8) if (!n13.resolveDisabled(t12[e8], e8, t12)) return e8;
      return r20;
    }
    case 3: {
      for (let e8 = t12.length - 1; e8 >= 0; --e8) if (!n13.resolveDisabled(t12[e8], e8, t12)) return e8;
      return r20;
    }
    case 4: {
      for (let e8 = 0; e8 < t12.length; ++e8) if (n13.resolveId(t12[e8], e8, t12) === l17.id) return e8;
      return r20;
    }
    case 5:
      return null;
    default:
      u14(l17);
  }
}

// node_modules/@headlessui/react/dist/components/mouse.js
var g3 = ((f22) => (f22[f22.Left = 0] = "Left", f22[f22.Right = 2] = "Right", f22))(g3 || {});

// node_modules/@headlessui/react/dist/components/portal/portal.js
var import_react84 = __toESM(require_react(), 1);
var import_react_dom7 = __toESM(require_react_dom(), 1);

// node_modules/@headlessui/react/dist/hooks/use-on-unmount.js
var import_react82 = __toESM(require_react(), 1);
function c11(t12) {
  let r20 = o5(t12), e8 = (0, import_react82.useRef)(false);
  (0, import_react82.useEffect)(() => (e8.current = false, () => {
    e8.current = true, t(() => {
      e8.current && r20();
    });
  }), [r20]);
}

// node_modules/@headlessui/react/dist/hooks/use-server-handoff-complete.js
var t8 = __toESM(require_react(), 1);
function s10() {
  let r20 = typeof document == "undefined";
  return "useSyncExternalStore" in t8 ? ((o21) => o21.useSyncExternalStore)(t8)(() => () => {
  }, () => false, () => !r20) : false;
}
function l10() {
  let r20 = s10(), [e8, n13] = t8.useState(s.isHandoffComplete);
  return e8 && s.isHandoffComplete === false && n13(false), t8.useEffect(() => {
    e8 !== true && n13(true);
  }, [e8]), t8.useEffect(() => s.handoff(), []), r20 ? false : e8;
}

// node_modules/@headlessui/react/dist/internal/portal-force-root.js
var import_react83 = __toESM(require_react(), 1);
var e7 = (0, import_react83.createContext)(false);
function a14() {
  return (0, import_react83.useContext)(e7);
}
function l11(o21) {
  return import_react83.default.createElement(e7.Provider, { value: o21.force }, o21.children);
}

// node_modules/@headlessui/react/dist/components/portal/portal.js
function j6(e8) {
  let l17 = a14(), o21 = (0, import_react84.useContext)(H7), [r20, u19] = (0, import_react84.useState)(() => {
    var i19;
    if (!l17 && o21 !== null) return (i19 = o21.current) != null ? i19 : null;
    if (s.isServer) return null;
    let t12 = e8 == null ? void 0 : e8.getElementById("headlessui-portal-root");
    if (t12) return t12;
    if (e8 === null) return null;
    let a25 = e8.createElement("div");
    return a25.setAttribute("id", "headlessui-portal-root"), e8.body.appendChild(a25);
  });
  return (0, import_react84.useEffect)(() => {
    r20 !== null && (e8 != null && e8.body.contains(r20) || e8 == null || e8.body.appendChild(r20));
  }, [r20, e8]), (0, import_react84.useEffect)(() => {
    l17 || o21 !== null && u19(o21.current);
  }, [o21, u19, l17]), r20;
}
var M3 = import_react84.Fragment;
var I3 = K(function(l17, o21) {
  let { ownerDocument: r20 = null, ...u19 } = l17, t12 = (0, import_react84.useRef)(null), a25 = y(T2((s15) => {
    t12.current = s15;
  }), o21), i19 = n9(t12), f22 = r20 != null ? r20 : i19, p6 = j6(f22), [n13] = (0, import_react84.useState)(() => {
    var s15;
    return s.isServer ? null : (s15 = f22 == null ? void 0 : f22.createElement("div")) != null ? s15 : null;
  }), P7 = (0, import_react84.useContext)(g4), b11 = l10();
  n(() => {
    !p6 || !n13 || p6.contains(n13) || (n13.setAttribute("data-headlessui-portal", ""), p6.appendChild(n13));
  }, [p6, n13]), n(() => {
    if (n13 && P7) return P7.register(n13);
  }, [P7, n13]), c11(() => {
    var s15;
    !p6 || !n13 || (n13 instanceof Node && p6.contains(n13) && p6.removeChild(n13), p6.childNodes.length <= 0 && ((s15 = p6.parentElement) == null || s15.removeChild(p6)));
  });
  let h11 = L();
  return b11 ? !p6 || !n13 ? null : (0, import_react_dom7.createPortal)(h11({ ourProps: { ref: a25 }, theirProps: u19, slot: {}, defaultTag: M3, name: "Portal" }), n13) : null;
});
function J3(e8, l17) {
  let o21 = y(l17), { enabled: r20 = true, ownerDocument: u19, ...t12 } = e8, a25 = L();
  return r20 ? import_react84.default.createElement(I3, { ...t12, ownerDocument: u19, ref: o21 }) : a25({ ourProps: { ref: o21 }, theirProps: t12, slot: {}, defaultTag: M3, name: "Portal" });
}
var X = import_react84.Fragment;
var H7 = (0, import_react84.createContext)(null);
function k3(e8, l17) {
  let { target: o21, ...r20 } = e8, t12 = { ref: y(l17) }, a25 = L();
  return import_react84.default.createElement(H7.Provider, { value: o21 }, a25({ ourProps: t12, theirProps: r20, defaultTag: X, name: "Popover.Group" }));
}
var g4 = (0, import_react84.createContext)(null);
function le() {
  let e8 = (0, import_react84.useContext)(g4), l17 = (0, import_react84.useRef)([]), o21 = o5((t12) => (l17.current.push(t12), e8 && e8.register(t12), () => r20(t12))), r20 = o5((t12) => {
    let a25 = l17.current.indexOf(t12);
    a25 !== -1 && l17.current.splice(a25, 1), e8 && e8.unregister(t12);
  }), u19 = (0, import_react84.useMemo)(() => ({ register: o21, unregister: r20, portals: l17 }), [o21, r20, l17]);
  return [l17, (0, import_react84.useMemo)(() => function({ children: a25 }) {
    return import_react84.default.createElement(g4.Provider, { value: u19 }, a25);
  }, [u19])];
}
var B = K(J3);
var D3 = K(k3);
var oe = Object.assign(B, { Group: D3 });

// node_modules/@headlessui/react/dist/components/combobox/combobox-machine.js
var I4 = Object.defineProperty;
var S7 = (t12, i19, e8) => i19 in t12 ? I4(t12, i19, { enumerable: true, configurable: true, writable: true, value: e8 }) : t12[i19] = e8;
var c14 = (t12, i19, e8) => (S7(t12, typeof i19 != "symbol" ? i19 + "" : i19, e8), e8);
var A5 = ((e8) => (e8[e8.Open = 0] = "Open", e8[e8.Closed = 1] = "Closed", e8))(A5 || {});
var E8 = ((e8) => (e8[e8.Single = 0] = "Single", e8[e8.Multi = 1] = "Multi", e8))(E8 || {});
var C6 = ((n13) => (n13[n13.Pointer = 0] = "Pointer", n13[n13.Focus = 1] = "Focus", n13[n13.Other = 2] = "Other", n13))(C6 || {});
var M4 = ((l17) => (l17[l17.OpenCombobox = 0] = "OpenCombobox", l17[l17.CloseCombobox = 1] = "CloseCombobox", l17[l17.GoToOption = 2] = "GoToOption", l17[l17.SetTyping = 3] = "SetTyping", l17[l17.RegisterOption = 4] = "RegisterOption", l17[l17.UnregisterOption = 5] = "UnregisterOption", l17[l17.DefaultToFirstOption = 6] = "DefaultToFirstOption", l17[l17.SetActivationTrigger = 7] = "SetActivationTrigger", l17[l17.UpdateVirtualConfiguration = 8] = "UpdateVirtualConfiguration", l17[l17.SetInputElement = 9] = "SetInputElement", l17[l17.SetButtonElement = 10] = "SetButtonElement", l17[l17.SetOptionsElement = 11] = "SetOptionsElement", l17))(M4 || {});
function v3(t12, i19 = (e8) => e8) {
  let e8 = t12.activeOptionIndex !== null ? t12.options[t12.activeOptionIndex] : null, n13 = i19(t12.options.slice()), o21 = n13.length > 0 && n13[0].dataRef.current.order !== null ? n13.sort((u19, a25) => u19.dataRef.current.order - a25.dataRef.current.order) : _3(n13, (u19) => u19.dataRef.current.domRef.current), r20 = e8 ? o21.indexOf(e8) : null;
  return r20 === -1 && (r20 = null), { options: o21, activeOptionIndex: r20 };
}
var F4 = { [1](t12) {
  var i19;
  return (i19 = t12.dataRef.current) != null && i19.disabled || t12.comboboxState === 1 ? t12 : { ...t12, activeOptionIndex: null, comboboxState: 1, isTyping: false, activationTrigger: 2, __demoMode: false };
}, [0](t12) {
  var i19, e8;
  if ((i19 = t12.dataRef.current) != null && i19.disabled || t12.comboboxState === 0) return t12;
  if ((e8 = t12.dataRef.current) != null && e8.value) {
    let n13 = t12.dataRef.current.calculateIndex(t12.dataRef.current.value);
    if (n13 !== -1) return { ...t12, activeOptionIndex: n13, comboboxState: 0, __demoMode: false };
  }
  return { ...t12, comboboxState: 0, __demoMode: false };
}, [3](t12, i19) {
  return t12.isTyping === i19.isTyping ? t12 : { ...t12, isTyping: i19.isTyping };
}, [2](t12, i19) {
  var r20, u19, a25, p6;
  if ((r20 = t12.dataRef.current) != null && r20.disabled || t12.optionsElement && !((u19 = t12.dataRef.current) != null && u19.optionsPropsRef.current.static) && t12.comboboxState === 1) return t12;
  if (t12.virtual) {
    let { options: d14, disabled: s15 } = t12.virtual, T12 = i19.focus === c10.Specific ? i19.idx : f15(i19, { resolveItems: () => d14, resolveActiveIndex: () => {
      var b11, m11;
      return (m11 = (b11 = t12.activeOptionIndex) != null ? b11 : d14.findIndex((y8) => !s15(y8))) != null ? m11 : null;
    }, resolveDisabled: s15, resolveId() {
      throw new Error("Function not implemented.");
    } }), l17 = (a25 = i19.trigger) != null ? a25 : 2;
    return t12.activeOptionIndex === T12 && t12.activationTrigger === l17 ? t12 : { ...t12, activeOptionIndex: T12, activationTrigger: l17, isTyping: false, __demoMode: false };
  }
  let e8 = v3(t12);
  if (e8.activeOptionIndex === null) {
    let d14 = e8.options.findIndex((s15) => !s15.dataRef.current.disabled);
    d14 !== -1 && (e8.activeOptionIndex = d14);
  }
  let n13 = i19.focus === c10.Specific ? i19.idx : f15(i19, { resolveItems: () => e8.options, resolveActiveIndex: () => e8.activeOptionIndex, resolveId: (d14) => d14.id, resolveDisabled: (d14) => d14.dataRef.current.disabled }), o21 = (p6 = i19.trigger) != null ? p6 : 2;
  return t12.activeOptionIndex === n13 && t12.activationTrigger === o21 ? t12 : { ...t12, ...e8, isTyping: false, activeOptionIndex: n13, activationTrigger: o21, __demoMode: false };
}, [4]: (t12, i19) => {
  var r20, u19, a25, p6;
  if ((r20 = t12.dataRef.current) != null && r20.virtual) return { ...t12, options: [...t12.options, i19.payload] };
  let e8 = i19.payload, n13 = v3(t12, (d14) => (d14.push(e8), d14));
  t12.activeOptionIndex === null && (a25 = (u19 = t12.dataRef.current).isSelected) != null && a25.call(u19, i19.payload.dataRef.current.value) && (n13.activeOptionIndex = n13.options.indexOf(e8));
  let o21 = { ...t12, ...n13, activationTrigger: 2 };
  return (p6 = t12.dataRef.current) != null && p6.__demoMode && t12.dataRef.current.value === void 0 && (o21.activeOptionIndex = 0), o21;
}, [5]: (t12, i19) => {
  var n13;
  if ((n13 = t12.dataRef.current) != null && n13.virtual) return { ...t12, options: t12.options.filter((o21) => o21.id !== i19.id) };
  let e8 = v3(t12, (o21) => {
    let r20 = o21.findIndex((u19) => u19.id === i19.id);
    return r20 !== -1 && o21.splice(r20, 1), o21;
  });
  return { ...t12, ...e8, activationTrigger: 2 };
}, [6]: (t12, i19) => t12.defaultToFirstOption === i19.value ? t12 : { ...t12, defaultToFirstOption: i19.value }, [7]: (t12, i19) => t12.activationTrigger === i19.trigger ? t12 : { ...t12, activationTrigger: i19.trigger }, [8]: (t12, i19) => {
  var n13, o21;
  if (t12.virtual === null) return { ...t12, virtual: { options: i19.options, disabled: (n13 = i19.disabled) != null ? n13 : () => false } };
  if (t12.virtual.options === i19.options && t12.virtual.disabled === i19.disabled) return t12;
  let e8 = t12.activeOptionIndex;
  if (t12.activeOptionIndex !== null) {
    let r20 = i19.options.indexOf(t12.virtual.options[t12.activeOptionIndex]);
    r20 !== -1 ? e8 = r20 : e8 = null;
  }
  return { ...t12, activeOptionIndex: e8, virtual: { options: i19.options, disabled: (o21 = i19.disabled) != null ? o21 : () => false } };
}, [9]: (t12, i19) => t12.inputElement === i19.element ? t12 : { ...t12, inputElement: i19.element }, [10]: (t12, i19) => t12.buttonElement === i19.element ? t12 : { ...t12, buttonElement: i19.element }, [11]: (t12, i19) => t12.optionsElement === i19.element ? t12 : { ...t12, optionsElement: i19.element } };
var O4 = class _O extends m9 {
  constructor() {
    super(...arguments);
    c14(this, "actions", { onChange: (e8) => {
      let { onChange: n13, compare: o21, mode: r20, value: u19 } = this.state.dataRef.current;
      return u(r20, { [0]: () => n13 == null ? void 0 : n13(e8), [1]: () => {
        let a25 = u19.slice(), p6 = a25.findIndex((d14) => o21(d14, e8));
        return p6 === -1 ? a25.push(e8) : a25.splice(p6, 1), n13 == null ? void 0 : n13(a25);
      } });
    }, registerOption: (e8, n13) => (this.send({ type: 4, payload: { id: e8, dataRef: n13 } }), () => {
      this.state.activeOptionIndex === this.state.dataRef.current.calculateIndex(n13.current.value) && this.send({ type: 6, value: true }), this.send({ type: 5, id: e8 });
    }), goToOption: (e8, n13) => (this.send({ type: 6, value: false }), this.send({ type: 2, ...e8, trigger: n13 })), setIsTyping: (e8) => {
      this.send({ type: 3, isTyping: e8 });
    }, closeCombobox: () => {
      var e8, n13;
      this.send({ type: 1 }), this.send({ type: 6, value: false }), (n13 = (e8 = this.state.dataRef.current).onClose) == null || n13.call(e8);
    }, openCombobox: () => {
      this.send({ type: 0 }), this.send({ type: 6, value: true });
    }, setActivationTrigger: (e8) => {
      this.send({ type: 7, trigger: e8 });
    }, selectActiveOption: () => {
      let e8 = this.selectors.activeOptionIndex(this.state);
      if (e8 !== null) {
        if (this.actions.setIsTyping(false), this.state.virtual) this.actions.onChange(this.state.virtual.options[e8]);
        else {
          let { dataRef: n13 } = this.state.options[e8];
          this.actions.onChange(n13.current.value);
        }
        this.actions.goToOption({ focus: c10.Specific, idx: e8 });
      }
    }, setInputElement: (e8) => {
      this.send({ type: 9, element: e8 });
    }, setButtonElement: (e8) => {
      this.send({ type: 10, element: e8 });
    }, setOptionsElement: (e8) => {
      this.send({ type: 11, element: e8 });
    } });
    c14(this, "selectors", { activeDescendantId: (e8) => {
      var o21, r20;
      let n13 = this.selectors.activeOptionIndex(e8);
      if (n13 !== null) return e8.virtual ? (r20 = e8.options.find((u19) => !u19.dataRef.current.disabled && e8.dataRef.current.compare(u19.dataRef.current.value, e8.virtual.options[n13]))) == null ? void 0 : r20.id : (o21 = e8.options[n13]) == null ? void 0 : o21.id;
    }, activeOptionIndex: (e8) => {
      if (e8.defaultToFirstOption && e8.activeOptionIndex === null && (e8.virtual ? e8.virtual.options.length > 0 : e8.options.length > 0)) {
        if (e8.virtual) {
          let { options: o21, disabled: r20 } = e8.virtual, u19 = o21.findIndex((a25) => {
            var p6;
            return !((p6 = r20 == null ? void 0 : r20(a25)) != null && p6);
          });
          if (u19 !== -1) return u19;
        }
        let n13 = e8.options.findIndex((o21) => !o21.dataRef.current.disabled);
        if (n13 !== -1) return n13;
      }
      return e8.activeOptionIndex;
    }, activeOption: (e8) => {
      var o21, r20;
      let n13 = this.selectors.activeOptionIndex(e8);
      return n13 === null ? null : e8.virtual ? e8.virtual.options[n13 != null ? n13 : 0] : (r20 = (o21 = e8.options[n13]) == null ? void 0 : o21.dataRef.current.value) != null ? r20 : null;
    }, isActive: (e8, n13, o21) => {
      var u19;
      let r20 = this.selectors.activeOptionIndex(e8);
      return r20 === null ? false : e8.virtual ? r20 === e8.dataRef.current.calculateIndex(n13) : ((u19 = e8.options[r20]) == null ? void 0 : u19.id) === o21;
    }, shouldScrollIntoView: (e8, n13, o21) => !(e8.virtual || e8.__demoMode || e8.comboboxState !== 0 || e8.activationTrigger === 0 || !this.selectors.isActive(e8, n13, o21)) });
  }
  static new({ virtual: e8 = null, __demoMode: n13 = false } = {}) {
    var o21;
    return new _O({ dataRef: { current: {} }, comboboxState: n13 ? 0 : 1, isTyping: false, options: [], virtual: e8 ? { options: e8.options, disabled: (o21 = e8.disabled) != null ? o21 : () => false } : null, activeOptionIndex: null, activationTrigger: 2, inputElement: null, buttonElement: null, optionsElement: null, __demoMode: n13 });
  }
  reduce(e8, n13) {
    return u(n13.type, F4, e8, n13);
  }
};

// node_modules/@headlessui/react/dist/components/combobox/combobox-machine-glue.js
var import_react85 = __toESM(require_react(), 1);
var b5 = (0, import_react85.createContext)(null);
function u16(e8) {
  let o21 = (0, import_react85.useContext)(b5);
  if (o21 === null) {
    let n13 = new Error(`<${e8} /> is missing a parent <Combobox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(n13, i13), n13;
  }
  return o21;
}
function i13({ virtual: e8 = null, __demoMode: o21 = false } = {}) {
  return (0, import_react85.useMemo)(() => O4.new({ virtual: e8, __demoMode: o21 }), []);
}

// node_modules/@headlessui/react/dist/components/combobox/combobox.js
var ue2 = (0, import_react86.createContext)(null);
ue2.displayName = "ComboboxDataContext";
function ne(y8) {
  let O9 = (0, import_react86.useContext)(ue2);
  if (O9 === null) {
    let e8 = new Error(`<${y8} /> is missing a parent <Combobox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(e8, ne), e8;
  }
  return O9;
}
var Se = (0, import_react86.createContext)(null);
function xo(y8) {
  let O9 = u16("VirtualProvider"), e8 = ne("VirtualProvider"), { options: o21 } = e8.virtual, V6 = S6(O9, (a25) => a25.optionsElement), [A8, C10] = (0, import_react86.useMemo)(() => {
    let a25 = V6;
    if (!a25) return [0, 0];
    let g8 = window.getComputedStyle(a25);
    return [parseFloat(g8.paddingBlockStart || g8.paddingTop), parseFloat(g8.paddingBlockEnd || g8.paddingBottom)];
  }, [V6]), b11 = useVirtualizer({ enabled: o21.length !== 0, scrollPaddingStart: A8, scrollPaddingEnd: C10, count: o21.length, estimateSize() {
    return 40;
  }, getScrollElement() {
    return O9.state.optionsElement;
  }, overscan: 12 }), [h11, l17] = (0, import_react86.useState)(0);
  n(() => {
    l17((a25) => a25 + 1);
  }, [o21]);
  let c19 = b11.getVirtualItems(), n13 = S6(O9, (a25) => a25.activationTrigger === C6.Pointer), x11 = S6(O9, O9.selectors.activeOptionIndex);
  return c19.length === 0 ? null : import_react86.default.createElement(Se.Provider, { value: b11 }, import_react86.default.createElement("div", { style: { position: "relative", width: "100%", height: `${b11.getTotalSize()}px` }, ref: (a25) => {
    a25 && (n13 || x11 !== null && o21.length > x11 && b11.scrollToIndex(x11));
  } }, c19.map((a25) => {
    var g8;
    return import_react86.default.createElement(import_react86.Fragment, { key: a25.key }, import_react86.default.cloneElement((g8 = y8.children) == null ? void 0 : g8.call(y8, { ...y8.slot, option: o21[a25.index] }), { key: `${h11}-${a25.key}`, "data-index": a25.index, "aria-setsize": o21.length, "aria-posinset": a25.index + 1, style: { position: "absolute", top: 0, left: 0, transform: `translateY(${a25.start}px)`, overflowAnchor: "none" } }));
  })));
}
var go = import_react86.Fragment;
function yo(y8, O9) {
  let e8 = a3(), { value: o21, defaultValue: V6, onChange: A8, form: C10, name: b11, by: h11, invalid: l17 = false, disabled: c19 = e8 || false, onClose: n13, __demoMode: x11 = false, multiple: a25 = false, immediate: g8 = false, virtual: f22 = null, nullable: B5, ...w10 } = y8, F9 = l2(V6), [m11 = a25 ? [] : void 0, P7] = T(o21, A8, F9), d14 = i13({ virtual: f22, __demoMode: x11 }), _8 = (0, import_react86.useRef)({ static: false, hold: false }), I7 = u8(h11), N3 = o5((p6) => f22 ? h11 === null ? f22.options.indexOf(p6) : f22.options.findIndex((T12) => I7(T12, p6)) : d14.state.options.findIndex((T12) => I7(T12.dataRef.current.value, p6))), z3 = (0, import_react86.useCallback)((p6) => u(i19.mode, { [E8.Multi]: () => m11.some((T12) => I7(T12, p6)), [E8.Single]: () => I7(m11, p6) }), [m11]), K4 = S6(d14, (p6) => p6.virtual), k5 = o5(() => n13 == null ? void 0 : n13()), i19 = (0, import_react86.useMemo)(() => ({ __demoMode: x11, immediate: g8, optionsPropsRef: _8, value: m11, defaultValue: F9, disabled: c19, invalid: l17, mode: a25 ? E8.Multi : E8.Single, virtual: f22 ? K4 : null, onChange: P7, isSelected: z3, calculateIndex: N3, compare: I7, onClose: k5 }), [m11, F9, c19, l17, a25, P7, z3, x11, d14, f22, K4, k5]);
  n(() => {
    var p6;
    f22 && d14.send({ type: M4.UpdateVirtualConfiguration, options: f22.options, disabled: (p6 = f22.disabled) != null ? p6 : null });
  }, [f22, f22 == null ? void 0 : f22.options, f22 == null ? void 0 : f22.disabled]), n(() => {
    d14.state.dataRef.current = i19;
  }, [i19]);
  let [S10, $4, U7, s15] = S6(d14, (p6) => [p6.comboboxState, p6.buttonElement, p6.inputElement, p6.optionsElement]), W3 = S10 === A5.Open;
  R3(W3, [$4, U7, s15], () => d14.actions.closeCombobox());
  let ae5 = S6(d14, d14.selectors.activeOptionIndex), J7 = S6(d14, d14.selectors.activeOption), q5 = (0, import_react86.useMemo)(() => ({ open: S10 === A5.Open, disabled: c19, invalid: l17, activeIndex: ae5, activeOption: J7, value: m11 }), [i19, c19, m11, l17, J7, S10]), [j8, pe5] = K2(), Y4 = O9 === null ? {} : { ref: O9 }, ee4 = (0, import_react86.useCallback)(() => {
    if (F9 !== void 0) return P7 == null ? void 0 : P7(F9);
  }, [P7, F9]), t12 = L();
  return import_react86.default.createElement(pe5, { value: j8, props: { htmlFor: U7 == null ? void 0 : U7.id }, slot: { open: S10 === A5.Open, disabled: c19 } }, import_react86.default.createElement(Me, null, import_react86.default.createElement(ue2.Provider, { value: i19 }, import_react86.default.createElement(b5.Provider, { value: d14 }, import_react86.default.createElement(c8, { value: u(S10, { [A5.Open]: i11.Open, [A5.Closed]: i11.Closed }) }, b11 != null && import_react86.default.createElement(j2, { disabled: c19, data: m11 != null ? { [b11]: m11 } : {}, form: C10, onReset: ee4 }), t12({ ourProps: Y4, theirProps: w10, slot: q5, defaultTag: go, name: "Combobox" }))))));
}
var Co = "input";
function vo(y8, O9) {
  var Y4, ee4;
  let e8 = u16("Combobox.Input"), o21 = ne("Combobox.Input"), V6 = (0, import_react50.useId)(), A8 = u4(), { id: C10 = A8 || `headlessui-combobox-input-${V6}`, onChange: b11, displayValue: h11, disabled: l17 = o21.disabled || false, autoFocus: c19 = false, type: n13 = "text", ...x11 } = y8, [a25] = S6(e8, (t12) => [t12.inputElement]), g8 = (0, import_react86.useRef)(null), f22 = y(g8, O9, ye(), e8.actions.setInputElement), B5 = n9(a25), [w10, F9] = S6(e8, (t12) => [t12.comboboxState, t12.isTyping]), m11 = p(), P7 = o5(() => {
    e8.actions.onChange(null), e8.state.optionsElement && (e8.state.optionsElement.scrollTop = 0), e8.actions.goToOption({ focus: c10.Nothing });
  }), d14 = (0, import_react86.useMemo)(() => {
    var t12;
    return typeof h11 == "function" && o21.value !== void 0 ? (t12 = h11(o21.value)) != null ? t12 : "" : typeof o21.value == "string" ? o21.value : "";
  }, [o21.value, h11]);
  m8(([t12, p6], [T12, H14]) => {
    if (e8.state.isTyping) return;
    let v5 = g8.current;
    v5 && ((H14 === A5.Open && p6 === A5.Closed || t12 !== T12) && (v5.value = t12), requestAnimationFrame(() => {
      if (e8.state.isTyping || !v5 || (B5 == null ? void 0 : B5.activeElement) !== v5) return;
      let { selectionStart: u19, selectionEnd: oe3 } = v5;
      Math.abs((oe3 != null ? oe3 : 0) - (u19 != null ? u19 : 0)) === 0 && u19 === 0 && v5.setSelectionRange(v5.value.length, v5.value.length);
    }));
  }, [d14, w10, B5, F9]), m8(([t12], [p6]) => {
    if (t12 === A5.Open && p6 === A5.Closed) {
      if (e8.state.isTyping) return;
      let T12 = g8.current;
      if (!T12) return;
      let H14 = T12.value, { selectionStart: v5, selectionEnd: u19, selectionDirection: oe3 } = T12;
      T12.value = "", T12.value = H14, oe3 !== null ? T12.setSelectionRange(v5, u19, oe3) : T12.setSelectionRange(v5, u19);
    }
  }, [w10]);
  let _8 = (0, import_react86.useRef)(false), I7 = o5(() => {
    _8.current = true;
  }), N3 = o5(() => {
    m11.nextFrame(() => {
      _8.current = false;
    });
  }), z3 = o5((t12) => {
    switch (e8.actions.setIsTyping(true), t12.key) {
      case o9.Enter:
        if (e8.state.comboboxState !== A5.Open || _8.current) return;
        if (t12.preventDefault(), t12.stopPropagation(), e8.selectors.activeOptionIndex(e8.state) === null) {
          e8.actions.closeCombobox();
          return;
        }
        e8.actions.selectActiveOption(), o21.mode === E8.Single && e8.actions.closeCombobox();
        break;
      case o9.ArrowDown:
        return t12.preventDefault(), t12.stopPropagation(), u(e8.state.comboboxState, { [A5.Open]: () => e8.actions.goToOption({ focus: c10.Next }), [A5.Closed]: () => e8.actions.openCombobox() });
      case o9.ArrowUp:
        return t12.preventDefault(), t12.stopPropagation(), u(e8.state.comboboxState, { [A5.Open]: () => e8.actions.goToOption({ focus: c10.Previous }), [A5.Closed]: () => {
          (0, import_react_dom8.flushSync)(() => e8.actions.openCombobox()), o21.value || e8.actions.goToOption({ focus: c10.Last });
        } });
      case o9.Home:
        if (t12.shiftKey) break;
        return t12.preventDefault(), t12.stopPropagation(), e8.actions.goToOption({ focus: c10.First });
      case o9.PageUp:
        return t12.preventDefault(), t12.stopPropagation(), e8.actions.goToOption({ focus: c10.First });
      case o9.End:
        if (t12.shiftKey) break;
        return t12.preventDefault(), t12.stopPropagation(), e8.actions.goToOption({ focus: c10.Last });
      case o9.PageDown:
        return t12.preventDefault(), t12.stopPropagation(), e8.actions.goToOption({ focus: c10.Last });
      case o9.Escape:
        return e8.state.comboboxState !== A5.Open ? void 0 : (t12.preventDefault(), e8.state.optionsElement && !o21.optionsPropsRef.current.static && t12.stopPropagation(), o21.mode === E8.Single && o21.value === null && P7(), e8.actions.closeCombobox());
      case o9.Tab:
        if (e8.state.comboboxState !== A5.Open) return;
        o21.mode === E8.Single && e8.state.activationTrigger !== C6.Focus && e8.actions.selectActiveOption(), e8.actions.closeCombobox();
        break;
    }
  }), K4 = o5((t12) => {
    b11 == null || b11(t12), o21.mode === E8.Single && t12.target.value === "" && P7(), e8.actions.openCombobox();
  }), k5 = o5((t12) => {
    var T12, H14, v5;
    let p6 = (T12 = t12.relatedTarget) != null ? T12 : r11.find((u19) => u19 !== t12.currentTarget);
    if (!((H14 = e8.state.optionsElement) != null && H14.contains(p6)) && !((v5 = e8.state.buttonElement) != null && v5.contains(p6)) && e8.state.comboboxState === A5.Open) return t12.preventDefault(), o21.mode === E8.Single && o21.value === null && P7(), e8.actions.closeCombobox();
  }), i19 = o5((t12) => {
    var T12, H14, v5;
    let p6 = (T12 = t12.relatedTarget) != null ? T12 : r11.find((u19) => u19 !== t12.currentTarget);
    (H14 = e8.state.buttonElement) != null && H14.contains(p6) || (v5 = e8.state.optionsElement) != null && v5.contains(p6) || o21.disabled || o21.immediate && e8.state.comboboxState !== A5.Open && m11.microTask(() => {
      (0, import_react_dom8.flushSync)(() => e8.actions.openCombobox()), e8.actions.setActivationTrigger(C6.Focus);
    });
  }), S10 = I(), $4 = U2(), { isFocused: U7, focusProps: s15 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: c19 }), { isHovered: W3, hoverProps: ae5 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: l17 }), J7 = S6(e8, (t12) => t12.optionsElement), q5 = (0, import_react86.useMemo)(() => ({ open: w10 === A5.Open, disabled: l17, invalid: o21.invalid, hover: W3, focus: U7, autofocus: c19 }), [o21, W3, U7, c19, l17, o21.invalid]), j8 = _({ ref: f22, id: C10, role: "combobox", type: n13, "aria-controls": J7 == null ? void 0 : J7.id, "aria-expanded": w10 === A5.Open, "aria-activedescendant": S6(e8, e8.selectors.activeDescendantId), "aria-labelledby": S10, "aria-describedby": $4, "aria-autocomplete": "list", defaultValue: (ee4 = (Y4 = y8.defaultValue) != null ? Y4 : o21.defaultValue !== void 0 ? h11 == null ? void 0 : h11(o21.defaultValue) : null) != null ? ee4 : o21.defaultValue, disabled: l17 || void 0, autoFocus: c19, onCompositionStart: I7, onCompositionEnd: N3, onKeyDown: z3, onChange: K4, onFocus: i19, onBlur: k5 }, s15, ae5);
  return L()({ ourProps: j8, theirProps: x11, slot: q5, defaultTag: Co, name: "Combobox.Input" });
}
var Po = "button";
function Eo(y8, O9) {
  let e8 = u16("Combobox.Button"), o21 = ne("Combobox.Button"), [V6, A8] = (0, import_react86.useState)(null), C10 = y(O9, A8, e8.actions.setButtonElement), b11 = (0, import_react50.useId)(), { id: h11 = `headlessui-combobox-button-${b11}`, disabled: l17 = o21.disabled || false, autoFocus: c19 = false, ...n13 } = y8, x11 = S6(e8, (i19) => i19.inputElement), a25 = i10(x11), g8 = o5((i19) => {
    switch (i19.key) {
      case o9.Space:
      case o9.Enter:
        i19.preventDefault(), i19.stopPropagation(), e8.state.comboboxState === A5.Closed && (0, import_react_dom8.flushSync)(() => e8.actions.openCombobox()), a25();
        return;
      case o9.ArrowDown:
        i19.preventDefault(), i19.stopPropagation(), e8.state.comboboxState === A5.Closed && ((0, import_react_dom8.flushSync)(() => e8.actions.openCombobox()), e8.state.dataRef.current.value || e8.actions.goToOption({ focus: c10.First })), a25();
        return;
      case o9.ArrowUp:
        i19.preventDefault(), i19.stopPropagation(), e8.state.comboboxState === A5.Closed && ((0, import_react_dom8.flushSync)(() => e8.actions.openCombobox()), e8.state.dataRef.current.value || e8.actions.goToOption({ focus: c10.Last })), a25();
        return;
      case o9.Escape:
        if (e8.state.comboboxState !== A5.Open) return;
        i19.preventDefault(), e8.state.optionsElement && !o21.optionsPropsRef.current.static && i19.stopPropagation(), (0, import_react_dom8.flushSync)(() => e8.actions.closeCombobox()), a25();
        return;
      default:
        return;
    }
  }), f22 = o5((i19) => {
    i19.preventDefault(), !r4(i19.currentTarget) && (i19.button === g3.Left && (e8.state.comboboxState === A5.Open ? e8.actions.closeCombobox() : e8.actions.openCombobox()), a25());
  }), B5 = I([h11]), { isFocusVisible: w10, focusProps: F9 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: c19 }), { isHovered: m11, hoverProps: P7 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: l17 }), { pressed: d14, pressProps: _8 } = w({ disabled: l17 }), [I7, N3] = S6(e8, (i19) => [i19.comboboxState, i19.optionsElement]), z3 = (0, import_react86.useMemo)(() => ({ open: I7 === A5.Open, active: d14 || I7 === A5.Open, disabled: l17, invalid: o21.invalid, value: o21.value, hover: m11, focus: w10 }), [o21, m11, w10, d14, l17, I7]), K4 = _({ ref: C10, id: h11, type: e6(y8, V6), tabIndex: -1, "aria-haspopup": "listbox", "aria-controls": N3 == null ? void 0 : N3.id, "aria-expanded": I7 === A5.Open, "aria-labelledby": B5, disabled: l17 || void 0, autoFocus: c19, onMouseDown: f22, onKeyDown: g8 }, F9, P7, _8);
  return L()({ ourProps: K4, theirProps: n13, slot: z3, defaultTag: Po, name: "Combobox.Button" });
}
var Oo = "div";
var ho = O.RenderStrategy | O.Static;
function Ao(y8, O9) {
  var T12, H14, v5;
  let e8 = (0, import_react50.useId)(), { id: o21 = `headlessui-combobox-options-${e8}`, hold: V6 = false, anchor: A8, portal: C10 = false, modal: b11 = true, transition: h11 = false, ...l17 } = y8, c19 = u16("Combobox.Options"), n13 = ne("Combobox.Options"), x11 = xe(A8);
  x11 && (C10 = true);
  let [a25, g8] = Re(x11), [f22, B5] = (0, import_react86.useState)(null), w10 = be(), F9 = y(O9, x11 ? a25 : null, c19.actions.setOptionsElement, B5), [m11, P7, d14, _8, I7] = S6(c19, (u19) => [u19.comboboxState, u19.inputElement, u19.buttonElement, u19.optionsElement, u19.activationTrigger]), N3 = n9(P7 || d14), z3 = n9(_8), K4 = u12(), [k5, i19] = x3(h11, f22, K4 !== null ? (K4 & i11.Open) === i11.Open : m11 === A5.Open);
  m6(k5, P7, c19.actions.closeCombobox);
  let S10 = n13.__demoMode ? false : b11 && m11 === A5.Open;
  f11(S10, z3);
  let $4 = n13.__demoMode ? false : b11 && m11 === A5.Open;
  y3($4, { allowed: (0, import_react86.useCallback)(() => [P7, d14, _8], [P7, d14, _8]) }), n(() => {
    var u19;
    n13.optionsPropsRef.current.static = (u19 = y8.static) != null ? u19 : false;
  }, [n13.optionsPropsRef, y8.static]), n(() => {
    n13.optionsPropsRef.current.hold = V6;
  }, [n13.optionsPropsRef, V6]), F3(m11 === A5.Open, { container: _8, accept(u19) {
    return u19.getAttribute("role") === "option" ? NodeFilter.FILTER_REJECT : u19.hasAttribute("role") ? NodeFilter.FILTER_SKIP : NodeFilter.FILTER_ACCEPT;
  }, walk(u19) {
    u19.setAttribute("role", "none");
  } });
  let U7 = I([d14 == null ? void 0 : d14.id]), s15 = (0, import_react86.useMemo)(() => ({ open: m11 === A5.Open, option: void 0 }), [m11]), W3 = o5(() => {
    c19.actions.setActivationTrigger(C6.Pointer);
  }), ae5 = o5((u19) => {
    u19.preventDefault(), c19.actions.setActivationTrigger(C6.Pointer);
  }), J7 = _(x11 ? w10() : {}, { "aria-labelledby": U7, role: "listbox", "aria-multiselectable": n13.mode === E8.Multi ? true : void 0, id: o21, ref: F9, style: { ...l17.style, ...g8, "--input-width": d3(P7, true).width, "--button-width": d3(d14, true).width }, onWheel: I7 === C6.Pointer ? void 0 : W3, onMouseDown: ae5, ...R4(i19) }), q5 = k5 && m11 === A5.Closed, j8 = l7(q5, (T12 = n13.virtual) == null ? void 0 : T12.options), pe5 = l7(q5, n13.value), Y4 = o5((u19) => n13.compare(pe5, u19)), ee4 = (0, import_react86.useMemo)(() => {
    if (!n13.virtual) return n13;
    if (j8 === void 0) throw new Error("Missing `options` in virtual mode");
    return j8 !== n13.virtual.options ? { ...n13, virtual: { ...n13.virtual, options: j8 } } : n13;
  }, [n13, j8, (H14 = n13.virtual) == null ? void 0 : H14.options]);
  n13.virtual && Object.assign(l17, { children: import_react86.default.createElement(ue2.Provider, { value: ee4 }, import_react86.default.createElement(xo, { slot: s15 }, l17.children)) });
  let t12 = L(), p6 = (0, import_react86.useMemo)(() => n13.mode === E8.Multi ? n13 : { ...n13, isSelected: Y4 }, [n13, Y4]);
  return import_react86.default.createElement(oe, { enabled: C10 ? y8.static || k5 : false, ownerDocument: N3 }, import_react86.default.createElement(ue2.Provider, { value: p6 }, t12({ ourProps: J7, theirProps: { ...l17, children: import_react86.default.createElement(f13, { freeze: q5 }, typeof l17.children == "function" ? (v5 = l17.children) == null ? void 0 : v5.call(l17, s15) : l17.children) }, slot: s15, defaultTag: Oo, features: ho, visible: k5, name: "Combobox.Options" })));
}
var Io = "div";
function Ro(y8, O9) {
  var S10, $4, U7;
  let e8 = ne("Combobox.Option"), o21 = u16("Combobox.Option"), V6 = (0, import_react50.useId)(), { id: A8 = `headlessui-combobox-option-${V6}`, value: C10, disabled: b11 = (U7 = ($4 = (S10 = e8.virtual) == null ? void 0 : S10.disabled) == null ? void 0 : $4.call(S10, C10)) != null ? U7 : false, order: h11 = null, ...l17 } = y8, [c19] = S6(o21, (s15) => [s15.inputElement]), n13 = i10(c19), x11 = S6(o21, (0, import_react86.useCallback)((s15) => o21.selectors.isActive(s15, C10, A8), [C10, A8])), a25 = e8.isSelected(C10), g8 = (0, import_react86.useRef)(null), f22 = s3({ disabled: b11, value: C10, domRef: g8, order: h11 }), B5 = (0, import_react86.useContext)(Se), w10 = y(O9, g8, B5 ? B5.measureElement : null), F9 = o5(() => {
    o21.actions.setIsTyping(false), o21.actions.onChange(C10);
  });
  n(() => o21.actions.registerOption(A8, f22), [f22, A8]);
  let m11 = S6(o21, (0, import_react86.useCallback)((s15) => o21.selectors.shouldScrollIntoView(s15, C10, A8), [C10, A8]));
  n(() => {
    if (m11) return o3().requestAnimationFrame(() => {
      var s15, W3;
      (W3 = (s15 = g8.current) == null ? void 0 : s15.scrollIntoView) == null || W3.call(s15, { block: "nearest" });
    });
  }, [m11, g8]);
  let P7 = o5((s15) => {
    s15.preventDefault(), s15.button === g3.Left && (b11 || (F9(), n8() || requestAnimationFrame(() => n13()), e8.mode === E8.Single && o21.actions.closeCombobox()));
  }), d14 = o5(() => {
    if (b11) return o21.actions.goToOption({ focus: c10.Nothing });
    let s15 = e8.calculateIndex(C10);
    o21.actions.goToOption({ focus: c10.Specific, idx: s15 });
  }), _8 = u10(), I7 = o5((s15) => _8.update(s15)), N3 = o5((s15) => {
    if (!_8.wasMoved(s15) || b11 || x11) return;
    let W3 = e8.calculateIndex(C10);
    o21.actions.goToOption({ focus: c10.Specific, idx: W3 }, C6.Pointer);
  }), z3 = o5((s15) => {
    _8.wasMoved(s15) && (b11 || x11 && (e8.optionsPropsRef.current.hold || o21.actions.goToOption({ focus: c10.Nothing })));
  }), K4 = (0, import_react86.useMemo)(() => ({ active: x11, focus: x11, selected: a25, disabled: b11 }), [x11, a25, b11]), k5 = { id: A8, ref: w10, role: "option", tabIndex: b11 === true ? void 0 : -1, "aria-disabled": b11 === true ? true : void 0, "aria-selected": a25, disabled: void 0, onMouseDown: P7, onFocus: d14, onPointerEnter: I7, onMouseEnter: I7, onPointerMove: N3, onMouseMove: N3, onPointerLeave: z3, onMouseLeave: z3 };
  return L()({ ourProps: k5, theirProps: l17, slot: K4, defaultTag: Io, name: "Combobox.Option" });
}
var _o = K(yo);
var Do = K(Eo);
var Fo = K(vo);
var So = Q;
var Mo = K(Ao);
var Lo = K(Ro);
var Dt = Object.assign(_o, { Input: Fo, Button: Do, Label: So, Options: Mo, Option: Lo });

// node_modules/@headlessui/react/dist/components/data-interactive/data-interactive.js
var import_react87 = __toESM(require_react(), 1);
var E9 = import_react87.Fragment;
function d10(o21, n13) {
  let { ...s15 } = o21, e8 = false, { isFocusVisible: t12, focusProps: p6 } = $f7dceffc5ad7768b$export$4e328f61c538687f(), { isHovered: r20, hoverProps: i19 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), { pressed: a25, pressProps: T12 } = w({ disabled: e8 }), l17 = _({ ref: n13 }, p6, i19, T12), c19 = (0, import_react87.useMemo)(() => ({ hover: r20, focus: t12, active: a25 }), [r20, t12, a25]);
  return L()({ ourProps: l17, theirProps: s15, slot: c19, defaultTag: E9, name: "DataInteractive" });
}
var x5 = K(d10);

// node_modules/@headlessui/react/dist/components/dialog/dialog.js
var import_react94 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-escape.js
function a16(o21, r20 = typeof document != "undefined" ? document.defaultView : null, t12) {
  let n13 = x2(o21, "escape");
  E5(r20, "keydown", (e8) => {
    n13 && (e8.defaultPrevented || e8.key === o9.Escape && t12(e8));
  });
}

// node_modules/@headlessui/react/dist/hooks/use-is-touch-device.js
var import_react88 = __toESM(require_react(), 1);
function f17() {
  var t12;
  let [e8] = (0, import_react88.useState)(() => typeof window != "undefined" && typeof window.matchMedia == "function" ? window.matchMedia("(pointer: coarse)") : null), [o21, c19] = (0, import_react88.useState)((t12 = e8 == null ? void 0 : e8.matches) != null ? t12 : false);
  return n(() => {
    if (!e8) return;
    function n13(r20) {
      c19(r20.matches);
    }
    return e8.addEventListener("change", n13), () => e8.removeEventListener("change", n13);
  }, [e8]), o21;
}

// node_modules/@headlessui/react/dist/hooks/use-root-containers.js
var import_react89 = __toESM(require_react(), 1);
function R6({ defaultContainers: l17 = [], portals: n13, mainTreeNode: o21 } = {}) {
  let r20 = n9(o21), u19 = o5(() => {
    var i19, c19;
    let t12 = [];
    for (let e8 of l17) e8 !== null && (e8 instanceof HTMLElement ? t12.push(e8) : "current" in e8 && e8.current instanceof HTMLElement && t12.push(e8.current));
    if (n13 != null && n13.current) for (let e8 of n13.current) t12.push(e8);
    for (let e8 of (i19 = r20 == null ? void 0 : r20.querySelectorAll("html > *, body > *")) != null ? i19 : []) e8 !== document.body && e8 !== document.head && e8 instanceof HTMLElement && e8.id !== "headlessui-portal-root" && (o21 && (e8.contains(o21) || e8.contains((c19 = o21 == null ? void 0 : o21.getRootNode()) == null ? void 0 : c19.host)) || t12.some((m11) => e8.contains(m11)) || t12.push(e8));
    return t12;
  });
  return { resolveContainers: u19, contains: o5((t12) => u19().some((i19) => i19.contains(t12))) };
}
var a17 = (0, import_react89.createContext)(null);
function O5({ children: l17, node: n13 }) {
  let [o21, r20] = (0, import_react89.useState)(null), u19 = b6(n13 != null ? n13 : o21);
  return import_react89.default.createElement(a17.Provider, { value: u19 }, l17, u19 === null && import_react89.default.createElement(f4, { features: s4.Hidden, ref: (t12) => {
    var i19, c19;
    if (t12) {
      for (let e8 of (c19 = (i19 = o2(t12)) == null ? void 0 : i19.querySelectorAll("html > *, body > *")) != null ? c19 : []) if (e8 !== document.body && e8 !== document.head && e8 instanceof HTMLElement && e8 != null && e8.contains(t12)) {
        r20(e8);
        break;
      }
    }
  } }));
}
function b6(l17 = null) {
  var n13;
  return (n13 = (0, import_react89.useContext)(a17)) != null ? n13 : l17;
}

// node_modules/@headlessui/react/dist/components/focus-trap/focus-trap.js
var import_react92 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-is-mounted.js
var import_react90 = __toESM(require_react(), 1);
function f19() {
  let e8 = (0, import_react90.useRef)(false);
  return n(() => (e8.current = true, () => {
    e8.current = false;
  }), []), e8;
}

// node_modules/@headlessui/react/dist/hooks/use-tab-direction.js
var import_react91 = __toESM(require_react(), 1);
var a18 = ((r20) => (r20[r20.Forwards = 0] = "Forwards", r20[r20.Backwards = 1] = "Backwards", r20))(a18 || {});
function u17() {
  let e8 = (0, import_react91.useRef)(0);
  return s5(true, "keydown", (r20) => {
    r20.key === "Tab" && (e8.current = r20.shiftKey ? 1 : 0);
  }, true), e8;
}

// node_modules/@headlessui/react/dist/components/focus-trap/focus-trap.js
function U4(o21) {
  if (!o21) return /* @__PURE__ */ new Set();
  if (typeof o21 == "function") return new Set(o21());
  let e8 = /* @__PURE__ */ new Set();
  for (let t12 of o21.current) t12.current instanceof HTMLElement && e8.add(t12.current);
  return e8;
}
var Z = "div";
var x6 = ((n13) => (n13[n13.None = 0] = "None", n13[n13.InitialFocus = 1] = "InitialFocus", n13[n13.TabLock = 2] = "TabLock", n13[n13.FocusLock = 4] = "FocusLock", n13[n13.RestoreFocus = 8] = "RestoreFocus", n13[n13.AutoFocus = 16] = "AutoFocus", n13))(x6 || {});
function $3(o21, e8) {
  let t12 = (0, import_react92.useRef)(null), r20 = y(t12, e8), { initialFocus: s15, initialFocusFallback: a25, containers: n13, features: u19 = 15, ...f22 } = o21;
  l10() || (u19 = 0);
  let l17 = n9(t12);
  ee(u19, { ownerDocument: l17 });
  let i19 = te2(u19, { ownerDocument: l17, container: t12, initialFocus: s15, initialFocusFallback: a25 });
  re2(u19, { ownerDocument: l17, container: t12, containers: n13, previousActiveElement: i19 });
  let R9 = u17(), g8 = o5((c19) => {
    let m11 = t12.current;
    if (!m11) return;
    ((G8) => G8())(() => {
      u(R9.current, { [a18.Forwards]: () => {
        P6(m11, F2.First, { skipElements: [c19.relatedTarget, a25] });
      }, [a18.Backwards]: () => {
        P6(m11, F2.Last, { skipElements: [c19.relatedTarget, a25] });
      } });
    });
  }), v5 = x2(!!(u19 & 2), "focus-trap#tab-lock"), N3 = p(), F9 = (0, import_react92.useRef)(false), k5 = { ref: r20, onKeyDown(c19) {
    c19.key == "Tab" && (F9.current = true, N3.requestAnimationFrame(() => {
      F9.current = false;
    }));
  }, onBlur(c19) {
    if (!(u19 & 4)) return;
    let m11 = U4(n13);
    t12.current instanceof HTMLElement && m11.add(t12.current);
    let d14 = c19.relatedTarget;
    d14 instanceof HTMLElement && d14.dataset.headlessuiFocusGuard !== "true" && (I5(m11, d14) || (F9.current ? P6(t12.current, u(R9.current, { [a18.Forwards]: () => F2.Next, [a18.Backwards]: () => F2.Previous }) | F2.WrapAround, { relativeTo: c19.target }) : c19.target instanceof HTMLElement && I2(c19.target)));
  } }, B5 = L();
  return import_react92.default.createElement(import_react92.default.Fragment, null, v5 && import_react92.default.createElement(f4, { as: "button", type: "button", "data-headlessui-focus-guard": true, onFocus: g8, features: s4.Focusable }), B5({ ourProps: k5, theirProps: f22, defaultTag: Z, name: "FocusTrap" }), v5 && import_react92.default.createElement(f4, { as: "button", type: "button", "data-headlessui-focus-guard": true, onFocus: g8, features: s4.Focusable }));
}
var D6 = K($3);
var ye2 = Object.assign(D6, { features: x6 });
function w6(o21 = true) {
  let e8 = (0, import_react92.useRef)(r11.slice());
  return m8(([t12], [r20]) => {
    r20 === true && t12 === false && t(() => {
      e8.current.splice(0);
    }), r20 === false && t12 === true && (e8.current = r11.slice());
  }, [o21, r11, e8]), o5(() => {
    var t12;
    return (t12 = e8.current.find((r20) => r20 != null && r20.isConnected)) != null ? t12 : null;
  });
}
function ee(o21, { ownerDocument: e8 }) {
  let t12 = !!(o21 & 8), r20 = w6(t12);
  m8(() => {
    t12 || (e8 == null ? void 0 : e8.activeElement) === (e8 == null ? void 0 : e8.body) && I2(r20());
  }, [t12]), c11(() => {
    t12 && I2(r20());
  });
}
function te2(o21, { ownerDocument: e8, container: t12, initialFocus: r20, initialFocusFallback: s15 }) {
  let a25 = (0, import_react92.useRef)(null), n13 = x2(!!(o21 & 1), "focus-trap#initial-focus"), u19 = f19();
  return m8(() => {
    if (o21 === 0) return;
    if (!n13) {
      s15 != null && s15.current && I2(s15.current);
      return;
    }
    let f22 = t12.current;
    f22 && t(() => {
      if (!u19.current) return;
      let l17 = e8 == null ? void 0 : e8.activeElement;
      if (r20 != null && r20.current) {
        if ((r20 == null ? void 0 : r20.current) === l17) {
          a25.current = l17;
          return;
        }
      } else if (f22.contains(l17)) {
        a25.current = l17;
        return;
      }
      if (r20 != null && r20.current) I2(r20.current);
      else {
        if (o21 & 16) {
          if (P6(f22, F2.First | F2.AutoFocus) !== T5.Error) return;
        } else if (P6(f22, F2.First) !== T5.Error) return;
        if (s15 != null && s15.current && (I2(s15.current), (e8 == null ? void 0 : e8.activeElement) === s15.current)) return;
        console.warn("There are no focusable elements inside the <FocusTrap />");
      }
      a25.current = e8 == null ? void 0 : e8.activeElement;
    });
  }, [s15, n13, o21]), a25;
}
function re2(o21, { ownerDocument: e8, container: t12, containers: r20, previousActiveElement: s15 }) {
  let a25 = f19(), n13 = !!(o21 & 4);
  E5(e8 == null ? void 0 : e8.defaultView, "focus", (u19) => {
    if (!n13 || !a25.current) return;
    let f22 = U4(r20);
    t12.current instanceof HTMLElement && f22.add(t12.current);
    let l17 = s15.current;
    if (!l17) return;
    let i19 = u19.target;
    i19 && i19 instanceof HTMLElement ? I5(f22, i19) ? (s15.current = i19, I2(i19)) : (u19.preventDefault(), u19.stopPropagation(), I2(l17)) : I2(s15.current);
  }, true);
}
function I5(o21, e8) {
  for (let t12 of o21) if (t12.contains(e8)) return true;
  return false;
}

// node_modules/@headlessui/react/dist/components/transition/transition.js
var import_react93 = __toESM(require_react(), 1);
function ue3(e8) {
  var t12;
  return !!(e8.enter || e8.enterFrom || e8.enterTo || e8.leave || e8.leaveFrom || e8.leaveTo) || ((t12 = e8.as) != null ? t12 : de3) !== import_react93.Fragment || import_react93.default.Children.count(e8.children) === 1;
}
var w7 = (0, import_react93.createContext)(null);
w7.displayName = "TransitionContext";
var _e = ((n13) => (n13.Visible = "visible", n13.Hidden = "hidden", n13))(_e || {});
function De() {
  let e8 = (0, import_react93.useContext)(w7);
  if (e8 === null) throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");
  return e8;
}
function He() {
  let e8 = (0, import_react93.useContext)(M7);
  if (e8 === null) throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");
  return e8;
}
var M7 = (0, import_react93.createContext)(null);
M7.displayName = "NestingContext";
function U5(e8) {
  return "children" in e8 ? U5(e8.children) : e8.current.filter(({ el: t12 }) => t12.current !== null).filter(({ state: t12 }) => t12 === "visible").length > 0;
}
function Te2(e8, t12) {
  let n13 = s3(e8), l17 = (0, import_react93.useRef)([]), S10 = f19(), R9 = p(), d14 = o5((o21, i19 = A.Hidden) => {
    let a25 = l17.current.findIndex(({ el: s15 }) => s15 === o21);
    a25 !== -1 && (u(i19, { [A.Unmount]() {
      l17.current.splice(a25, 1);
    }, [A.Hidden]() {
      l17.current[a25].state = "hidden";
    } }), R9.microTask(() => {
      var s15;
      !U5(l17) && S10.current && ((s15 = n13.current) == null || s15.call(n13));
    }));
  }), y8 = o5((o21) => {
    let i19 = l17.current.find(({ el: a25 }) => a25 === o21);
    return i19 ? i19.state !== "visible" && (i19.state = "visible") : l17.current.push({ el: o21, state: "visible" }), () => d14(o21, A.Unmount);
  }), C10 = (0, import_react93.useRef)([]), p6 = (0, import_react93.useRef)(Promise.resolve()), h11 = (0, import_react93.useRef)({ enter: [], leave: [] }), g8 = o5((o21, i19, a25) => {
    C10.current.splice(0), t12 && (t12.chains.current[i19] = t12.chains.current[i19].filter(([s15]) => s15 !== o21)), t12 == null || t12.chains.current[i19].push([o21, new Promise((s15) => {
      C10.current.push(s15);
    })]), t12 == null || t12.chains.current[i19].push([o21, new Promise((s15) => {
      Promise.all(h11.current[i19].map(([r20, f22]) => f22)).then(() => s15());
    })]), i19 === "enter" ? p6.current = p6.current.then(() => t12 == null ? void 0 : t12.wait.current).then(() => a25(i19)) : a25(i19);
  }), v5 = o5((o21, i19, a25) => {
    Promise.all(h11.current[i19].splice(0).map(([s15, r20]) => r20)).then(() => {
      var s15;
      (s15 = C10.current.shift()) == null || s15();
    }).then(() => a25(i19));
  });
  return (0, import_react93.useMemo)(() => ({ children: l17, register: y8, unregister: d14, onStart: g8, onStop: v5, wait: p6, chains: h11 }), [y8, d14, l17, g8, v5, h11, p6]);
}
var de3 = import_react93.Fragment;
var fe = O.RenderStrategy;
function Ae(e8, t12) {
  var ee4, te6;
  let { transition: n13 = true, beforeEnter: l17, afterEnter: S10, beforeLeave: R9, afterLeave: d14, enter: y8, enterFrom: C10, enterTo: p6, entered: h11, leave: g8, leaveFrom: v5, leaveTo: o21, ...i19 } = e8, [a25, s15] = (0, import_react93.useState)(null), r20 = (0, import_react93.useRef)(null), f22 = ue3(e8), j8 = y(...f22 ? [r20, t12, s15] : t12 === null ? [] : [t12]), H14 = (ee4 = i19.unmount) == null || ee4 ? A.Unmount : A.Hidden, { show: u19, appear: z3, initial: K4 } = De(), [m11, G8] = (0, import_react93.useState)(u19 ? "visible" : "hidden"), Q4 = He(), { register: A8, unregister: I7 } = Q4;
  n(() => A8(r20), [A8, r20]), n(() => {
    if (H14 === A.Hidden && r20.current) {
      if (u19 && m11 !== "visible") {
        G8("visible");
        return;
      }
      return u(m11, { ["hidden"]: () => I7(r20), ["visible"]: () => A8(r20) });
    }
  }, [m11, r20, A8, I7, u19, H14]);
  let B5 = l10();
  n(() => {
    if (f22 && B5 && m11 === "visible" && r20.current === null) throw new Error("Did you forget to passthrough the `ref` to the actual DOM node?");
  }, [r20, m11, B5, f22]);
  let ce5 = K4 && !z3, Y4 = z3 && u19 && K4, W3 = (0, import_react93.useRef)(false), L7 = Te2(() => {
    W3.current || (G8("hidden"), I7(r20));
  }, Q4), Z4 = o5((k5) => {
    W3.current = true;
    let F9 = k5 ? "enter" : "leave";
    L7.onStart(r20, F9, (_8) => {
      _8 === "enter" ? l17 == null || l17() : _8 === "leave" && (R9 == null || R9());
    });
  }), $4 = o5((k5) => {
    let F9 = k5 ? "enter" : "leave";
    W3.current = false, L7.onStop(r20, F9, (_8) => {
      _8 === "enter" ? S10 == null || S10() : _8 === "leave" && (d14 == null || d14());
    }), F9 === "leave" && !U5(L7) && (G8("hidden"), I7(r20));
  });
  (0, import_react93.useEffect)(() => {
    f22 && n13 || (Z4(u19), $4(u19));
  }, [u19, f22, n13]);
  let pe5 = /* @__PURE__ */ (() => !(!n13 || !f22 || !B5 || ce5))(), [, T12] = x3(pe5, a25, u19, { start: Z4, end: $4 }), Ce4 = m2({ ref: j8, className: ((te6 = t3(i19.className, Y4 && y8, Y4 && C10, T12.enter && y8, T12.enter && T12.closed && C10, T12.enter && !T12.closed && p6, T12.leave && g8, T12.leave && !T12.closed && v5, T12.leave && T12.closed && o21, !T12.transition && u19 && h11)) == null ? void 0 : te6.trim()) || void 0, ...R4(T12) }), N3 = 0;
  m11 === "visible" && (N3 |= i11.Open), m11 === "hidden" && (N3 |= i11.Closed), u19 && m11 === "hidden" && (N3 |= i11.Opening), !u19 && m11 === "visible" && (N3 |= i11.Closing);
  let he4 = L();
  return import_react93.default.createElement(M7.Provider, { value: L7 }, import_react93.default.createElement(c8, { value: N3 }, he4({ ourProps: Ce4, theirProps: i19, defaultTag: de3, features: fe, visible: m11 === "visible", name: "Transition.Child" })));
}
function Ie(e8, t12) {
  let { show: n13, appear: l17 = false, unmount: S10 = true, ...R9 } = e8, d14 = (0, import_react93.useRef)(null), y8 = ue3(e8), C10 = y(...y8 ? [d14, t12] : t12 === null ? [] : [t12]);
  l10();
  let p6 = u12();
  if (n13 === void 0 && p6 !== null && (n13 = (p6 & i11.Open) === i11.Open), n13 === void 0) throw new Error("A <Transition /> is used but it is missing a `show={true | false}` prop.");
  let [h11, g8] = (0, import_react93.useState)(n13 ? "visible" : "hidden"), v5 = Te2(() => {
    n13 || g8("hidden");
  }), [o21, i19] = (0, import_react93.useState)(true), a25 = (0, import_react93.useRef)([n13]);
  n(() => {
    o21 !== false && a25.current[a25.current.length - 1] !== n13 && (a25.current.push(n13), i19(false));
  }, [a25, n13]);
  let s15 = (0, import_react93.useMemo)(() => ({ show: n13, appear: l17, initial: o21 }), [n13, l17, o21]);
  n(() => {
    n13 ? g8("visible") : !U5(v5) && d14.current !== null && g8("hidden");
  }, [n13, v5]);
  let r20 = { unmount: S10 }, f22 = o5(() => {
    var u19;
    o21 && i19(false), (u19 = e8.beforeEnter) == null || u19.call(e8);
  }), j8 = o5(() => {
    var u19;
    o21 && i19(false), (u19 = e8.beforeLeave) == null || u19.call(e8);
  }), H14 = L();
  return import_react93.default.createElement(M7.Provider, { value: v5 }, import_react93.default.createElement(w7.Provider, { value: s15 }, H14({ ourProps: { ...r20, as: import_react93.Fragment, children: import_react93.default.createElement(me, { ref: C10, ...r20, ...R9, beforeEnter: f22, beforeLeave: j8 }) }, theirProps: {}, defaultTag: import_react93.Fragment, features: fe, visible: h11 === "visible", name: "Transition" })));
}
function Le(e8, t12) {
  let n13 = (0, import_react93.useContext)(w7) !== null, l17 = u12() !== null;
  return import_react93.default.createElement(import_react93.default.Fragment, null, !n13 && l17 ? import_react93.default.createElement(X3, { ref: t12, ...e8 }) : import_react93.default.createElement(me, { ref: t12, ...e8 }));
}
var X3 = K(Ie);
var me = K(Ae);
var Fe3 = K(Le);
var ze = Object.assign(X3, { Child: Fe3, Root: X3 });

// node_modules/@headlessui/react/dist/components/dialog/dialog.js
var Oe2 = ((o21) => (o21[o21.Open = 0] = "Open", o21[o21.Closed = 1] = "Closed", o21))(Oe2 || {});
var he = ((t12) => (t12[t12.SetTitleId = 0] = "SetTitleId", t12))(he || {});
var Se2 = { [0](e8, t12) {
  return e8.titleId === t12.id ? e8 : { ...e8, titleId: t12.id };
} };
var k4 = (0, import_react94.createContext)(null);
k4.displayName = "DialogContext";
function O7(e8) {
  let t12 = (0, import_react94.useContext)(k4);
  if (t12 === null) {
    let o21 = new Error(`<${e8} /> is missing a parent <Dialog /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(o21, O7), o21;
  }
  return t12;
}
function Ie2(e8, t12) {
  return u(t12.type, Se2, e8, t12);
}
var V3 = K(function(t12, o21) {
  let a25 = (0, import_react50.useId)(), { id: l17 = `headlessui-dialog-${a25}`, open: i19, onClose: p6, initialFocus: d14, role: s15 = "dialog", autoFocus: f22 = true, __demoMode: u19 = false, unmount: P7 = false, ...h11 } = t12, R9 = (0, import_react94.useRef)(false);
  s15 = function() {
    return s15 === "dialog" || s15 === "alertdialog" ? s15 : (R9.current || (R9.current = true, console.warn(`Invalid role [${s15}] passed to <Dialog />. Only \`dialog\` and and \`alertdialog\` are supported. Using \`dialog\` instead.`)), "dialog");
  }();
  let c19 = u12();
  i19 === void 0 && c19 !== null && (i19 = (c19 & i11.Open) === i11.Open);
  let T12 = (0, import_react94.useRef)(null), S10 = y(T12, o21), F9 = n9(T12), g8 = i19 ? 0 : 1, [b11, q5] = (0, import_react94.useReducer)(Ie2, { titleId: null, descriptionId: null, panelRef: (0, import_react94.createRef)() }), m11 = o5(() => p6(false)), w10 = o5((r20) => q5({ type: 0, id: r20 })), D8 = l10() ? g8 === 0 : false, [z3, Q4] = le(), Z4 = { get current() {
    var r20;
    return (r20 = b11.panelRef.current) != null ? r20 : T12.current;
  } }, v5 = b6(), { resolveContainers: I7 } = R6({ mainTreeNode: v5, portals: z3, defaultContainers: [Z4] }), B5 = c19 !== null ? (c19 & i11.Closing) === i11.Closing : false;
  y3(u19 || B5 ? false : D8, { allowed: o5(() => {
    var r20, H14;
    return [(H14 = (r20 = T12.current) == null ? void 0 : r20.closest("[data-headlessui-portal]")) != null ? H14 : null];
  }), disallowed: o5(() => {
    var r20;
    return [(r20 = v5 == null ? void 0 : v5.closest("body > *:not(#headlessui-portal-root)")) != null ? r20 : null];
  }) }), R3(D8, I7, (r20) => {
    r20.preventDefault(), m11();
  }), a16(D8, F9 == null ? void 0 : F9.defaultView, (r20) => {
    r20.preventDefault(), r20.stopPropagation(), document.activeElement && "blur" in document.activeElement && typeof document.activeElement.blur == "function" && document.activeElement.blur(), m11();
  }), f11(u19 || B5 ? false : D8, F9, I7), m6(D8, T12, m11);
  let [ee4, te6] = w3(), oe3 = (0, import_react94.useMemo)(() => [{ dialogState: g8, close: m11, setTitleId: w10, unmount: P7 }, b11], [g8, b11, m11, w10, P7]), U7 = (0, import_react94.useMemo)(() => ({ open: g8 === 0 }), [g8]), ne4 = { ref: S10, id: l17, role: s15, tabIndex: -1, "aria-modal": u19 ? void 0 : g8 === 0 ? true : void 0, "aria-labelledby": b11.titleId, "aria-describedby": ee4, unmount: P7 }, re6 = !f17(), y8 = x6.None;
  D8 && !u19 && (y8 |= x6.RestoreFocus, y8 |= x6.TabLock, f22 && (y8 |= x6.AutoFocus), re6 && (y8 |= x6.InitialFocus));
  let le5 = L();
  return import_react94.default.createElement(s7, null, import_react94.default.createElement(l11, { force: true }, import_react94.default.createElement(oe, null, import_react94.default.createElement(k4.Provider, { value: oe3 }, import_react94.default.createElement(D3, { target: T12 }, import_react94.default.createElement(l11, { force: false }, import_react94.default.createElement(te6, { slot: U7 }, import_react94.default.createElement(Q4, null, import_react94.default.createElement(ye2, { initialFocus: d14, initialFocusFallback: T12, containers: I7, features: y8 }, import_react94.default.createElement(C4, { value: m11 }, le5({ ourProps: ne4, theirProps: h11, slot: U7, defaultTag: Me2, features: Ge, visible: g8 === 0, name: "Dialog" })))))))))));
});
var Me2 = "div";
var Ge = O.RenderStrategy | O.Static;
function ke(e8, t12) {
  let { transition: o21 = false, open: a25, ...l17 } = e8, i19 = u12(), p6 = e8.hasOwnProperty("open") || i19 !== null, d14 = e8.hasOwnProperty("onClose");
  if (!p6 && !d14) throw new Error("You have to provide an `open` and an `onClose` prop to the `Dialog` component.");
  if (!p6) throw new Error("You provided an `onClose` prop to the `Dialog`, but forgot an `open` prop.");
  if (!d14) throw new Error("You provided an `open` prop to the `Dialog`, but forgot an `onClose` prop.");
  if (!i19 && typeof e8.open != "boolean") throw new Error(`You provided an \`open\` prop to the \`Dialog\`, but the value is not a boolean. Received: ${e8.open}`);
  if (typeof e8.onClose != "function") throw new Error(`You provided an \`onClose\` prop to the \`Dialog\`, but the value is not a function. Received: ${e8.onClose}`);
  return (a25 !== void 0 || o21) && !l17.static ? import_react94.default.createElement(O5, null, import_react94.default.createElement(ze, { show: a25, transition: o21, unmount: l17.unmount }, import_react94.default.createElement(V3, { ref: t12, ...l17 }))) : import_react94.default.createElement(O5, null, import_react94.default.createElement(V3, { ref: t12, open: a25, ...l17 }));
}
var we = "div";
function Be(e8, t12) {
  let o21 = (0, import_react50.useId)(), { id: a25 = `headlessui-dialog-panel-${o21}`, transition: l17 = false, ...i19 } = e8, [{ dialogState: p6, unmount: d14 }, s15] = O7("Dialog.Panel"), f22 = y(t12, s15.panelRef), u19 = (0, import_react94.useMemo)(() => ({ open: p6 === 0 }), [p6]), P7 = o5((S10) => {
    S10.stopPropagation();
  }), h11 = { ref: f22, id: a25, onClick: P7 }, R9 = l17 ? Fe3 : import_react94.Fragment, c19 = l17 ? { unmount: d14 } : {}, T12 = L();
  return import_react94.default.createElement(R9, { ...c19 }, T12({ ourProps: h11, theirProps: i19, slot: u19, defaultTag: we, name: "Dialog.Panel" }));
}
var Ue = "div";
function He2(e8, t12) {
  let { transition: o21 = false, ...a25 } = e8, [{ dialogState: l17, unmount: i19 }] = O7("Dialog.Backdrop"), p6 = (0, import_react94.useMemo)(() => ({ open: l17 === 0 }), [l17]), d14 = { ref: t12, "aria-hidden": true }, s15 = o21 ? Fe3 : import_react94.Fragment, f22 = o21 ? { unmount: i19 } : {}, u19 = L();
  return import_react94.default.createElement(s15, { ...f22 }, u19({ ourProps: d14, theirProps: a25, slot: p6, defaultTag: Ue, name: "Dialog.Backdrop" }));
}
var Ne = "h2";
function We(e8, t12) {
  let o21 = (0, import_react50.useId)(), { id: a25 = `headlessui-dialog-title-${o21}`, ...l17 } = e8, [{ dialogState: i19, setTitleId: p6 }] = O7("Dialog.Title"), d14 = y(t12);
  (0, import_react94.useEffect)(() => (p6(a25), () => p6(null)), [a25, p6]);
  let s15 = (0, import_react94.useMemo)(() => ({ open: i19 === 0 }), [i19]), f22 = { ref: d14, id: a25 };
  return L()({ ourProps: f22, theirProps: l17, slot: s15, defaultTag: Ne, name: "Dialog.Title" });
}
var $e = K(ke);
var je = K(Be);
var Dt2 = K(He2);
var Ye = K(We);
var Pt = H4;
var yt = Object.assign($e, { Panel: je, Title: Ye, Description: H4 });

// node_modules/@headlessui/react/dist/components/disclosure/disclosure.js
var import_react96 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/start-transition.js
var import_react95 = __toESM(require_react(), 1);
var t11;
var a19 = (t11 = import_react95.default.startTransition) != null ? t11 : function(i19) {
  i19();
};

// node_modules/@headlessui/react/dist/components/disclosure/disclosure.js
var ce2 = ((l17) => (l17[l17.Open = 0] = "Open", l17[l17.Closed = 1] = "Closed", l17))(ce2 || {});
var de5 = ((n13) => (n13[n13.ToggleDisclosure = 0] = "ToggleDisclosure", n13[n13.CloseDisclosure = 1] = "CloseDisclosure", n13[n13.SetButtonId = 2] = "SetButtonId", n13[n13.SetPanelId = 3] = "SetPanelId", n13[n13.SetButtonElement = 4] = "SetButtonElement", n13[n13.SetPanelElement = 5] = "SetPanelElement", n13))(de5 || {});
var Te3 = { [0]: (e8) => ({ ...e8, disclosureState: u(e8.disclosureState, { [0]: 1, [1]: 0 }) }), [1]: (e8) => e8.disclosureState === 1 ? e8 : { ...e8, disclosureState: 1 }, [2](e8, t12) {
  return e8.buttonId === t12.buttonId ? e8 : { ...e8, buttonId: t12.buttonId };
}, [3](e8, t12) {
  return e8.panelId === t12.panelId ? e8 : { ...e8, panelId: t12.panelId };
}, [4](e8, t12) {
  return e8.buttonElement === t12.element ? e8 : { ...e8, buttonElement: t12.element };
}, [5](e8, t12) {
  return e8.panelElement === t12.element ? e8 : { ...e8, panelElement: t12.element };
} };
var _5 = (0, import_react96.createContext)(null);
_5.displayName = "DisclosureContext";
function M8(e8) {
  let t12 = (0, import_react96.useContext)(_5);
  if (t12 === null) {
    let l17 = new Error(`<${e8} /> is missing a parent <Disclosure /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(l17, M8), l17;
  }
  return t12;
}
var F5 = (0, import_react96.createContext)(null);
F5.displayName = "DisclosureAPIContext";
function J4(e8) {
  let t12 = (0, import_react96.useContext)(F5);
  if (t12 === null) {
    let l17 = new Error(`<${e8} /> is missing a parent <Disclosure /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(l17, J4), l17;
  }
  return t12;
}
var H9 = (0, import_react96.createContext)(null);
H9.displayName = "DisclosurePanelContext";
function fe2() {
  return (0, import_react96.useContext)(H9);
}
function me2(e8, t12) {
  return u(t12.type, Te3, e8, t12);
}
var De2 = import_react96.Fragment;
function ye3(e8, t12) {
  let { defaultOpen: l17 = false, ...p6 } = e8, i19 = (0, import_react96.useRef)(null), c19 = y(t12, T2((a25) => {
    i19.current = a25;
  }, e8.as === void 0 || e8.as === import_react96.Fragment)), n13 = (0, import_react96.useReducer)(me2, { disclosureState: l17 ? 0 : 1, buttonElement: null, panelElement: null, buttonId: null, panelId: null }), [{ disclosureState: o21, buttonId: r20 }, m11] = n13, s15 = o5((a25) => {
    m11({ type: 1 });
    let d14 = o2(i19);
    if (!d14 || !r20) return;
    let T12 = (() => a25 ? a25 instanceof HTMLElement ? a25 : a25.current instanceof HTMLElement ? a25.current : d14.getElementById(r20) : d14.getElementById(r20))();
    T12 == null || T12.focus();
  }), E15 = (0, import_react96.useMemo)(() => ({ close: s15 }), [s15]), f22 = (0, import_react96.useMemo)(() => ({ open: o21 === 0, close: s15 }), [o21, s15]), D8 = { ref: c19 }, S10 = L();
  return import_react96.default.createElement(_5.Provider, { value: n13 }, import_react96.default.createElement(F5.Provider, { value: E15 }, import_react96.default.createElement(C4, { value: s15 }, import_react96.default.createElement(c8, { value: u(o21, { [0]: i11.Open, [1]: i11.Closed }) }, S10({ ourProps: D8, theirProps: p6, slot: f22, defaultTag: De2, name: "Disclosure" })))));
}
var Pe2 = "button";
function Ee2(e8, t12) {
  let l17 = (0, import_react50.useId)(), { id: p6 = `headlessui-disclosure-button-${l17}`, disabled: i19 = false, autoFocus: c19 = false, ...n13 } = e8, [o21, r20] = M8("Disclosure.Button"), m11 = fe2(), s15 = m11 === null ? false : m11 === o21.panelId, E15 = (0, import_react96.useRef)(null), f22 = y(E15, t12, o5((u19) => {
    if (!s15) return r20({ type: 4, element: u19 });
  }));
  (0, import_react96.useEffect)(() => {
    if (!s15) return r20({ type: 2, buttonId: p6 }), () => {
      r20({ type: 2, buttonId: null });
    };
  }, [p6, r20, s15]);
  let D8 = o5((u19) => {
    var g8;
    if (s15) {
      if (o21.disclosureState === 1) return;
      switch (u19.key) {
        case o9.Space:
        case o9.Enter:
          u19.preventDefault(), u19.stopPropagation(), r20({ type: 0 }), (g8 = o21.buttonElement) == null || g8.focus();
          break;
      }
    } else switch (u19.key) {
      case o9.Space:
      case o9.Enter:
        u19.preventDefault(), u19.stopPropagation(), r20({ type: 0 });
        break;
    }
  }), S10 = o5((u19) => {
    switch (u19.key) {
      case o9.Space:
        u19.preventDefault();
        break;
    }
  }), a25 = o5((u19) => {
    var g8;
    r4(u19.currentTarget) || i19 || (s15 ? (r20({ type: 0 }), (g8 = o21.buttonElement) == null || g8.focus()) : r20({ type: 0 }));
  }), { isFocusVisible: d14, focusProps: T12 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: c19 }), { isHovered: b11, hoverProps: h11 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: i19 }), { pressed: U7, pressProps: N3 } = w({ disabled: i19 }), X6 = (0, import_react96.useMemo)(() => ({ open: o21.disclosureState === 0, hover: b11, active: U7, disabled: i19, focus: d14, autofocus: c19 }), [o21, b11, U7, d14, i19, c19]), k5 = e6(e8, o21.buttonElement), V6 = s15 ? _({ ref: f22, type: k5, disabled: i19 || void 0, autoFocus: c19, onKeyDown: D8, onClick: a25 }, T12, h11, N3) : _({ ref: f22, id: p6, type: k5, "aria-expanded": o21.disclosureState === 0, "aria-controls": o21.panelElement ? o21.panelId : void 0, disabled: i19 || void 0, autoFocus: c19, onKeyDown: D8, onKeyUp: S10, onClick: a25 }, T12, h11, N3);
  return L()({ ourProps: V6, theirProps: n13, slot: X6, defaultTag: Pe2, name: "Disclosure.Button" });
}
var Se3 = "div";
var ge2 = O.RenderStrategy | O.Static;
function Ae2(e8, t12) {
  let l17 = (0, import_react50.useId)(), { id: p6 = `headlessui-disclosure-panel-${l17}`, transition: i19 = false, ...c19 } = e8, [n13, o21] = M8("Disclosure.Panel"), { close: r20 } = J4("Disclosure.Panel"), [m11, s15] = (0, import_react96.useState)(null), E15 = y(t12, o5((b11) => {
    a19(() => o21({ type: 5, element: b11 }));
  }), s15);
  (0, import_react96.useEffect)(() => (o21({ type: 3, panelId: p6 }), () => {
    o21({ type: 3, panelId: null });
  }), [p6, o21]);
  let f22 = u12(), [D8, S10] = x3(i19, m11, f22 !== null ? (f22 & i11.Open) === i11.Open : n13.disclosureState === 0), a25 = (0, import_react96.useMemo)(() => ({ open: n13.disclosureState === 0, close: r20 }), [n13.disclosureState, r20]), d14 = { ref: E15, id: p6, ...R4(S10) }, T12 = L();
  return import_react96.default.createElement(s7, null, import_react96.default.createElement(H9.Provider, { value: n13.panelId }, T12({ ourProps: d14, theirProps: c19, slot: a25, defaultTag: Se3, features: ge2, visible: D8, name: "Disclosure.Panel" })));
}
var be2 = K(ye3);
var Ce = K(Ee2);
var Re2 = K(Ae2);
var je2 = Object.assign(be2, { Button: Ce, Panel: Re2 });

// node_modules/@headlessui/react/dist/components/field/field.js
var import_react97 = __toESM(require_react(), 1);
var _6 = "div";
function c16(d14, l17) {
  let t12 = `headlessui-control-${(0, import_react50.useId)()}`, [s15, p6] = K2(), [n13, a25] = w3(), m11 = a3(), { disabled: e8 = m11 || false, ...i19 } = d14, o21 = (0, import_react97.useMemo)(() => ({ disabled: e8 }), [e8]), F9 = { ref: l17, disabled: e8 || void 0, "aria-disabled": e8 || void 0 }, T12 = L();
  return import_react97.default.createElement(l, { value: e8 }, import_react97.default.createElement(p6, { value: s15 }, import_react97.default.createElement(a25, { value: n13 }, import_react97.default.createElement(f6, { id: t12 }, T12({ ourProps: F9, theirProps: { ...i19, children: import_react97.default.createElement(W, null, typeof i19.children == "function" ? i19.children(o21) : i19.children) }, slot: o21, defaultTag: _6, name: "Field" })))));
}
var H10 = K(c16);

// node_modules/@headlessui/react/dist/components/fieldset/fieldset.js
var import_react99 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/hooks/use-resolved-tag.js
var import_react98 = __toESM(require_react(), 1);
function l12(t12) {
  let e8 = typeof t12 == "string" ? t12 : void 0, [s15, o21] = (0, import_react98.useState)(e8);
  return [e8 != null ? e8 : s15, (0, import_react98.useCallback)((n13) => {
    e8 || n13 instanceof HTMLElement && o21(n13.tagName.toLowerCase());
  }, [e8])];
}

// node_modules/@headlessui/react/dist/components/fieldset/fieldset.js
var d12 = "fieldset";
function _7(t12, a25) {
  var s15;
  let i19 = a3(), { disabled: e8 = i19 || false, ...p6 } = t12, [n13, T12] = l12((s15 = t12.as) != null ? s15 : d12), l17 = y(a25, T12), [r20, f22] = K2(), m11 = (0, import_react99.useMemo)(() => ({ disabled: e8 }), [e8]), y8 = n13 === "fieldset" ? { ref: l17, "aria-labelledby": r20, disabled: e8 || void 0 } : { ref: l17, role: "group", "aria-labelledby": r20, "aria-disabled": e8 || void 0 }, F9 = L();
  return import_react99.default.createElement(l, { value: e8 }, import_react99.default.createElement(f22, null, F9({ ourProps: y8, theirProps: p6, slot: m11, defaultTag: d12, name: "Fieldset" })));
}
var G5 = K(_7);

// node_modules/@headlessui/react/dist/components/input/input.js
var import_react100 = __toESM(require_react(), 1);
var x8 = "input";
function h7(p6, s15) {
  let a25 = (0, import_react50.useId)(), l17 = u4(), i19 = a3(), { id: d14 = l17 || `headlessui-input-${a25}`, disabled: e8 = i19 || false, autoFocus: o21 = false, invalid: t12 = false, ...u19 } = p6, f22 = I(), m11 = U2(), { isFocused: r20, focusProps: T12 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: o21 }), { isHovered: n13, hoverProps: b11 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), y8 = _({ ref: s15, id: d14, "aria-labelledby": f22, "aria-describedby": m11, "aria-invalid": t12 ? "true" : void 0, disabled: e8 || void 0, autoFocus: o21 }, T12, b11), I7 = (0, import_react100.useMemo)(() => ({ disabled: e8, invalid: t12, hover: n13, focus: r20, autofocus: o21 }), [e8, t12, n13, r20, o21]);
  return L()({ ourProps: y8, theirProps: u19, slot: I7, defaultTag: x8, name: "Input" });
}
var S8 = K(h7);

// node_modules/@headlessui/react/dist/components/legend/legend.js
var import_react101 = __toESM(require_react(), 1);
function o17(t12, n13) {
  return import_react101.default.createElement(Q, { as: "div", ref: n13, ...t12 });
}
var d13 = K(o17);

// node_modules/@headlessui/react/dist/components/listbox/listbox.js
var import_react105 = __toESM(require_react(), 1);
var import_react_dom9 = __toESM(require_react_dom(), 1);

// node_modules/@headlessui/react/dist/hooks/use-did-element-move.js
var import_react102 = __toESM(require_react(), 1);
function s11(n13, t12) {
  let e8 = (0, import_react102.useRef)({ left: 0, top: 0 });
  if (n(() => {
    if (!t12) return;
    let r20 = t12.getBoundingClientRect();
    r20 && (e8.current = r20);
  }, [n13, t12]), t12 == null || !n13 || t12 === document.activeElement) return false;
  let o21 = t12.getBoundingClientRect();
  return o21.top !== e8.current.top || o21.left !== e8.current.left;
}

// node_modules/@headlessui/react/dist/hooks/use-text-value.js
var import_react103 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/utils/get-text-value.js
var a21 = /([\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF])/g;
function o18(e8) {
  var r20, i19;
  let n13 = (r20 = e8.innerText) != null ? r20 : "", t12 = e8.cloneNode(true);
  if (!(t12 instanceof HTMLElement)) return n13;
  let u19 = false;
  for (let f22 of t12.querySelectorAll('[hidden],[aria-hidden],[role="img"]')) f22.remove(), u19 = true;
  let l17 = u19 ? (i19 = t12.innerText) != null ? i19 : "" : n13;
  return a21.test(l17) && (l17 = l17.replace(a21, "")), l17;
}
function g6(e8) {
  let n13 = e8.getAttribute("aria-label");
  if (typeof n13 == "string") return n13.trim();
  let t12 = e8.getAttribute("aria-labelledby");
  if (t12) {
    let u19 = t12.split(" ").map((l17) => {
      let r20 = document.getElementById(l17);
      if (r20) {
        let i19 = r20.getAttribute("aria-label");
        return typeof i19 == "string" ? i19.trim() : o18(r20).trim();
      }
      return null;
    }).filter(Boolean);
    if (u19.length > 0) return u19.join(", ");
  }
  return o18(e8).trim();
}

// node_modules/@headlessui/react/dist/hooks/use-text-value.js
function s12(c19) {
  let t12 = (0, import_react103.useRef)(""), r20 = (0, import_react103.useRef)("");
  return o5(() => {
    let e8 = c19.current;
    if (!e8) return "";
    let u19 = e8.innerText;
    if (t12.current === u19) return r20.current;
    let n13 = g6(e8).trim().toLowerCase();
    return t12.current = u19, r20.current = n13, n13;
  });
}

// node_modules/@headlessui/react/dist/components/listbox/listbox-machine.js
var g7 = Object.defineProperty;
var h8 = (e8, i19, t12) => i19 in e8 ? g7(e8, i19, { enumerable: true, configurable: true, writable: true, value: t12 }) : e8[i19] = t12;
var O8 = (e8, i19, t12) => (h8(e8, typeof i19 != "symbol" ? i19 + "" : i19, t12), t12);
var R7 = ((t12) => (t12[t12.Open = 0] = "Open", t12[t12.Closed = 1] = "Closed", t12))(R7 || {});
var A6 = ((t12) => (t12[t12.Single = 0] = "Single", t12[t12.Multi = 1] = "Multi", t12))(A6 || {});
var E11 = ((t12) => (t12[t12.Pointer = 0] = "Pointer", t12[t12.Other = 1] = "Other", t12))(E11 || {});
var L5 = ((r20) => (r20[r20.OpenListbox = 0] = "OpenListbox", r20[r20.CloseListbox = 1] = "CloseListbox", r20[r20.GoToOption = 2] = "GoToOption", r20[r20.Search = 3] = "Search", r20[r20.ClearSearch = 4] = "ClearSearch", r20[r20.RegisterOptions = 5] = "RegisterOptions", r20[r20.UnregisterOptions = 6] = "UnregisterOptions", r20[r20.SetButtonElement = 7] = "SetButtonElement", r20[r20.SetOptionsElement = 8] = "SetOptionsElement", r20[r20.SortOptions = 9] = "SortOptions", r20))(L5 || {});
function m10(e8, i19 = (t12) => t12) {
  let t12 = e8.activeOptionIndex !== null ? e8.options[e8.activeOptionIndex] : null, n13 = _3(i19(e8.options.slice()), (l17) => l17.dataRef.current.domRef.current), o21 = t12 ? n13.indexOf(t12) : null;
  return o21 === -1 && (o21 = null), { options: n13, activeOptionIndex: o21 };
}
var M9 = { [1](e8) {
  return e8.dataRef.current.disabled || e8.listboxState === 1 ? e8 : { ...e8, activeOptionIndex: null, listboxState: 1, __demoMode: false };
}, [0](e8) {
  if (e8.dataRef.current.disabled || e8.listboxState === 0) return e8;
  let i19 = e8.activeOptionIndex, { isSelected: t12 } = e8.dataRef.current, n13 = e8.options.findIndex((o21) => t12(o21.dataRef.current.value));
  return n13 !== -1 && (i19 = n13), { ...e8, listboxState: 0, activeOptionIndex: i19, __demoMode: false };
}, [2](e8, i19) {
  var l17, s15, u19, d14, c19;
  if (e8.dataRef.current.disabled || e8.listboxState === 1) return e8;
  let t12 = { ...e8, searchQuery: "", activationTrigger: (l17 = i19.trigger) != null ? l17 : 1, __demoMode: false };
  if (i19.focus === c10.Nothing) return { ...t12, activeOptionIndex: null };
  if (i19.focus === c10.Specific) return { ...t12, activeOptionIndex: e8.options.findIndex((r20) => r20.id === i19.id) };
  if (i19.focus === c10.Previous) {
    let r20 = e8.activeOptionIndex;
    if (r20 !== null) {
      let x11 = e8.options[r20].dataRef.current.domRef, a25 = f15(i19, { resolveItems: () => e8.options, resolveActiveIndex: () => e8.activeOptionIndex, resolveId: (p6) => p6.id, resolveDisabled: (p6) => p6.dataRef.current.disabled });
      if (a25 !== null) {
        let p6 = e8.options[a25].dataRef.current.domRef;
        if (((s15 = x11.current) == null ? void 0 : s15.previousElementSibling) === p6.current || ((u19 = p6.current) == null ? void 0 : u19.previousElementSibling) === null) return { ...t12, activeOptionIndex: a25 };
      }
    }
  } else if (i19.focus === c10.Next) {
    let r20 = e8.activeOptionIndex;
    if (r20 !== null) {
      let x11 = e8.options[r20].dataRef.current.domRef, a25 = f15(i19, { resolveItems: () => e8.options, resolveActiveIndex: () => e8.activeOptionIndex, resolveId: (p6) => p6.id, resolveDisabled: (p6) => p6.dataRef.current.disabled });
      if (a25 !== null) {
        let p6 = e8.options[a25].dataRef.current.domRef;
        if (((d14 = x11.current) == null ? void 0 : d14.nextElementSibling) === p6.current || ((c19 = p6.current) == null ? void 0 : c19.nextElementSibling) === null) return { ...t12, activeOptionIndex: a25 };
      }
    }
  }
  let n13 = m10(e8), o21 = f15(i19, { resolveItems: () => n13.options, resolveActiveIndex: () => n13.activeOptionIndex, resolveId: (r20) => r20.id, resolveDisabled: (r20) => r20.dataRef.current.disabled });
  return { ...t12, ...n13, activeOptionIndex: o21 };
}, [3]: (e8, i19) => {
  if (e8.dataRef.current.disabled || e8.listboxState === 1) return e8;
  let n13 = e8.searchQuery !== "" ? 0 : 1, o21 = e8.searchQuery + i19.value.toLowerCase(), s15 = (e8.activeOptionIndex !== null ? e8.options.slice(e8.activeOptionIndex + n13).concat(e8.options.slice(0, e8.activeOptionIndex + n13)) : e8.options).find((d14) => {
    var c19;
    return !d14.dataRef.current.disabled && ((c19 = d14.dataRef.current.textValue) == null ? void 0 : c19.startsWith(o21));
  }), u19 = s15 ? e8.options.indexOf(s15) : -1;
  return u19 === -1 || u19 === e8.activeOptionIndex ? { ...e8, searchQuery: o21 } : { ...e8, searchQuery: o21, activeOptionIndex: u19, activationTrigger: 1 };
}, [4](e8) {
  return e8.dataRef.current.disabled || e8.listboxState === 1 || e8.searchQuery === "" ? e8 : { ...e8, searchQuery: "" };
}, [5]: (e8, i19) => {
  let t12 = e8.options.concat(i19.options), n13 = e8.activeOptionIndex;
  if (e8.activeOptionIndex === null) {
    let { isSelected: o21 } = e8.dataRef.current;
    if (o21) {
      let l17 = t12.findIndex((s15) => o21 == null ? void 0 : o21(s15.dataRef.current.value));
      l17 !== -1 && (n13 = l17);
    }
  }
  return { ...e8, options: t12, activeOptionIndex: n13, pendingShouldSort: true };
}, [6]: (e8, i19) => {
  let t12 = e8.options, n13 = [], o21 = new Set(i19.options);
  for (let [l17, s15] of t12.entries()) if (o21.has(s15.id) && (n13.push(l17), o21.delete(s15.id), o21.size === 0)) break;
  if (n13.length > 0) {
    t12 = t12.slice();
    for (let l17 of n13.reverse()) t12.splice(l17, 1);
  }
  return { ...e8, options: t12, activationTrigger: 1 };
}, [7]: (e8, i19) => e8.buttonElement === i19.element ? e8 : { ...e8, buttonElement: i19.element }, [8]: (e8, i19) => e8.optionsElement === i19.element ? e8 : { ...e8, optionsElement: i19.element }, [9]: (e8) => e8.pendingShouldSort ? { ...e8, ...m10(e8), pendingShouldSort: false } : e8 };
var T10 = class _T extends m9 {
  constructor(t12) {
    super(t12);
    O8(this, "actions", { onChange: (t13) => {
      let { onChange: n13, compare: o21, mode: l17, value: s15 } = this.state.dataRef.current;
      return u(l17, { [0]: () => n13 == null ? void 0 : n13(t13), [1]: () => {
        let u19 = s15.slice(), d14 = u19.findIndex((c19) => o21(c19, t13));
        return d14 === -1 ? u19.push(t13) : u19.splice(d14, 1), n13 == null ? void 0 : n13(u19);
      } });
    }, registerOption: g2(() => {
      let t13 = [], n13 = /* @__PURE__ */ new Set();
      return [(o21, l17) => {
        n13.has(l17) || (n13.add(l17), t13.push({ id: o21, dataRef: l17 }));
      }, () => (n13.clear(), this.send({ type: 5, options: t13.splice(0) }))];
    }), unregisterOption: g2(() => {
      let t13 = [];
      return [(n13) => t13.push(n13), () => {
        this.send({ type: 6, options: t13.splice(0) });
      }];
    }), goToOption: g2(() => {
      let t13 = null;
      return [(n13, o21) => {
        t13 = { type: 2, ...n13, trigger: o21 };
      }, () => t13 && this.send(t13)];
    }), closeListbox: () => {
      this.send({ type: 1 });
    }, openListbox: () => {
      this.send({ type: 0 });
    }, selectActiveOption: () => {
      if (this.state.activeOptionIndex !== null) {
        let { dataRef: t13, id: n13 } = this.state.options[this.state.activeOptionIndex];
        this.actions.onChange(t13.current.value), this.send({ type: 2, focus: c10.Specific, id: n13 });
      }
    }, selectOption: (t13) => {
      let n13 = this.state.options.find((o21) => o21.id === t13);
      n13 && this.actions.onChange(n13.dataRef.current.value);
    }, search: (t13) => {
      this.send({ type: 3, value: t13 });
    }, clearSearch: () => {
      this.send({ type: 4 });
    }, setButtonElement: (t13) => {
      this.send({ type: 7, element: t13 });
    }, setOptionsElement: (t13) => {
      this.send({ type: 8, element: t13 });
    } });
    O8(this, "selectors", { activeDescendantId(t13) {
      var l17;
      let n13 = t13.activeOptionIndex, o21 = t13.options;
      return n13 === null || (l17 = o21[n13]) == null ? void 0 : l17.id;
    }, isActive(t13, n13) {
      var s15;
      let o21 = t13.activeOptionIndex, l17 = t13.options;
      return o21 !== null ? ((s15 = l17[o21]) == null ? void 0 : s15.id) === n13 : false;
    }, shouldScrollIntoView(t13, n13) {
      return t13.__demoMode || t13.listboxState !== 0 || t13.activationTrigger === 0 ? false : this.isActive(t13, n13);
    } });
    this.on(5, () => {
      requestAnimationFrame(() => {
        this.send({ type: 9 });
      });
    });
  }
  static new({ __demoMode: t12 = false } = {}) {
    return new _T({ dataRef: { current: {} }, listboxState: t12 ? 0 : 1, options: [], searchQuery: "", activeOptionIndex: null, activationTrigger: 1, buttonElement: null, optionsElement: null, __demoMode: t12 });
  }
  reduce(t12, n13) {
    return u(n13.type, M9, t12, n13);
  }
};

// node_modules/@headlessui/react/dist/components/listbox/listbox-machine-glue.js
var import_react104 = __toESM(require_react(), 1);
var c17 = (0, import_react104.createContext)(null);
function l14(t12) {
  let e8 = (0, import_react104.useContext)(c17);
  if (e8 === null) {
    let n13 = new Error(`<${t12} /> is missing a parent <Listbox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(n13, a22), n13;
  }
  return e8;
}
function a22({ __demoMode: t12 = false } = {}) {
  return (0, import_react104.useMemo)(() => T10.new({ __demoMode: t12 }), []);
}

// node_modules/@headlessui/react/dist/components/listbox/listbox.js
var Z2 = (0, import_react105.createContext)(null);
Z2.displayName = "ListboxDataContext";
function q3(x11) {
  let P7 = (0, import_react105.useContext)(Z2);
  if (P7 === null) {
    let g8 = new Error(`<${x11} /> is missing a parent <Listbox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(g8, q3), g8;
  }
  return P7;
}
var xt = import_react105.Fragment;
function Ot(x11, P7) {
  let g8 = a3(), { value: p6, defaultValue: l17, form: i19, name: E15, onChange: s15, by: T12, invalid: t12 = false, disabled: u19 = g8 || false, horizontal: S10 = false, multiple: a25 = false, __demoMode: o21 = false, ...d14 } = x11;
  const O9 = S10 ? "horizontal" : "vertical";
  let v5 = y(P7), A8 = l2(l17), [b11 = a25 ? [] : void 0, m11] = T(p6, s15, A8), y8 = a22({ __demoMode: o21 }), h11 = (0, import_react105.useRef)({ static: false, hold: false }), B5 = (0, import_react105.useRef)(/* @__PURE__ */ new Map()), I7 = u8(T12), U7 = (0, import_react105.useCallback)((R9) => u(k5.mode, { [A6.Multi]: () => b11.some((z3) => I7(z3, R9)), [A6.Single]: () => I7(b11, R9) }), [b11]), k5 = (0, import_react105.useMemo)(() => ({ value: b11, disabled: u19, invalid: t12, mode: a25 ? A6.Multi : A6.Single, orientation: O9, onChange: m11, compare: I7, isSelected: U7, optionsPropsRef: h11, listRef: B5 }), [b11, u19, t12, a25, O9, m11, I7, U7, h11, B5]);
  n(() => {
    y8.state.dataRef.current = k5;
  }, [k5]);
  let n13 = S6(y8, (R9) => R9.listboxState), _8 = n13 === R7.Open, [L7, W3] = S6(y8, (R9) => [R9.buttonElement, R9.optionsElement]);
  R3(_8, [L7, W3], (R9, z3) => {
    y8.send({ type: L5.CloseListbox }), A2(z3, h5.Loose) || (R9.preventDefault(), L7 == null || L7.focus());
  });
  let V6 = (0, import_react105.useMemo)(() => ({ open: n13 === R7.Open, disabled: u19, invalid: t12, value: b11 }), [n13, u19, t12, b11]), [r20, K4] = K2({ inherit: true }), ee4 = { ref: v5 }, te6 = (0, import_react105.useCallback)(() => {
    if (A8 !== void 0) return m11 == null ? void 0 : m11(A8);
  }, [m11, A8]), oe3 = L();
  return import_react105.default.createElement(K4, { value: r20, props: { htmlFor: L7 == null ? void 0 : L7.id }, slot: { open: n13 === R7.Open, disabled: u19 } }, import_react105.default.createElement(Me, null, import_react105.default.createElement(c17.Provider, { value: y8 }, import_react105.default.createElement(Z2.Provider, { value: k5 }, import_react105.default.createElement(c8, { value: u(n13, { [R7.Open]: i11.Open, [R7.Closed]: i11.Closed }) }, E15 != null && b11 != null && import_react105.default.createElement(j2, { disabled: u19, data: { [E15]: b11 }, form: i19, onReset: te6 }), oe3({ ourProps: ee4, theirProps: d14, slot: V6, defaultTag: xt, name: "Listbox" }))))));
}
var Lt = "button";
function Pt2(x11, P7) {
  let g8 = (0, import_react50.useId)(), p6 = u4(), l17 = q3("Listbox.Button"), i19 = l14("Listbox.Button"), { id: E15 = p6 || `headlessui-listbox-button-${g8}`, disabled: s15 = l17.disabled || false, autoFocus: T12 = false, ...t12 } = x11, u19 = y(P7, ye(), i19.actions.setButtonElement), S10 = Fe2(), a25 = o5((r20) => {
    switch (r20.key) {
      case o9.Enter:
        p2(r20.currentTarget);
        break;
      case o9.Space:
      case o9.ArrowDown:
        r20.preventDefault(), (0, import_react_dom9.flushSync)(() => i19.actions.openListbox()), l17.value || i19.actions.goToOption({ focus: c10.First });
        break;
      case o9.ArrowUp:
        r20.preventDefault(), (0, import_react_dom9.flushSync)(() => i19.actions.openListbox()), l17.value || i19.actions.goToOption({ focus: c10.Last });
        break;
    }
  }), o21 = o5((r20) => {
    switch (r20.key) {
      case o9.Space:
        r20.preventDefault();
        break;
    }
  }), d14 = o5((r20) => {
    var K4;
    if (r20.button === 0) {
      if (r4(r20.currentTarget)) return r20.preventDefault();
      i19.state.listboxState === R7.Open ? ((0, import_react_dom9.flushSync)(() => i19.actions.closeListbox()), (K4 = i19.state.buttonElement) == null || K4.focus({ preventScroll: true })) : (r20.preventDefault(), i19.actions.openListbox());
    }
  }), O9 = o5((r20) => r20.preventDefault()), v5 = I([E15]), A8 = U2(), { isFocusVisible: b11, focusProps: m11 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: T12 }), { isHovered: y8, hoverProps: h11 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: s15 }), { pressed: B5, pressProps: I7 } = w({ disabled: s15 }), U7 = S6(i19, (r20) => r20.listboxState), k5 = (0, import_react105.useMemo)(() => ({ open: U7 === R7.Open, active: B5 || U7 === R7.Open, disabled: s15, invalid: l17.invalid, value: l17.value, hover: y8, focus: b11, autofocus: T12 }), [U7, l17.value, s15, y8, b11, B5, l17.invalid, T12]), n13 = S6(i19, (r20) => r20.listboxState === R7.Open), [_8, L7] = S6(i19, (r20) => [r20.buttonElement, r20.optionsElement]), W3 = _(S10(), { ref: u19, id: E15, type: e6(x11, _8), "aria-haspopup": "listbox", "aria-controls": L7 == null ? void 0 : L7.id, "aria-expanded": n13, "aria-labelledby": v5, "aria-describedby": A8, disabled: s15 || void 0, autoFocus: T12, onKeyDown: a25, onKeyUp: o21, onKeyPress: O9, onMouseDown: d14 }, m11, h11, I7);
  return L()({ ourProps: W3, theirProps: t12, slot: k5, defaultTag: Lt, name: "Listbox.Button" });
}
var ye4 = (0, import_react105.createContext)(false);
var gt = "div";
var Et = O.RenderStrategy | O.Static;
function vt(x11, P7) {
  let g8 = (0, import_react50.useId)(), { id: p6 = `headlessui-listbox-options-${g8}`, anchor: l17, portal: i19 = false, modal: E15 = true, transition: s15 = false, ...T12 } = x11, t12 = xe(l17), [u19, S10] = (0, import_react105.useState)(null);
  t12 && (i19 = true);
  let a25 = q3("Listbox.Options"), o21 = l14("Listbox.Options"), [d14, O9, v5, A8] = S6(o21, (e8) => [e8.listboxState, e8.buttonElement, e8.optionsElement, e8.__demoMode]), b11 = n9(O9), m11 = n9(v5), y8 = u12(), [h11, B5] = x3(s15, u19, y8 !== null ? (y8 & i11.Open) === i11.Open : d14 === R7.Open);
  m6(h11, O9, o21.actions.closeListbox);
  let I7 = A8 ? false : E15 && d14 === R7.Open;
  f11(I7, m11);
  let U7 = A8 ? false : E15 && d14 === R7.Open;
  y3(U7, { allowed: (0, import_react105.useCallback)(() => [O9, v5], [O9, v5]) });
  let k5 = d14 !== R7.Open, _8 = s11(k5, O9) ? false : h11, L7 = h11 && d14 === R7.Closed, W3 = l7(L7, a25.value), V6 = o5((e8) => a25.compare(W3, e8)), r20 = S6(o21, (e8) => {
    var X6;
    if (t12 == null || !((X6 = t12 == null ? void 0 : t12.to) != null && X6.includes("selection"))) return null;
    let D8 = e8.options.findIndex((ne4) => V6(ne4.dataRef.current.value));
    return D8 === -1 && (D8 = 0), D8;
  }), K4 = (() => {
    if (t12 == null) return;
    if (r20 === null) return { ...t12, inner: void 0 };
    let e8 = Array.from(a25.listRef.current.values());
    return { ...t12, inner: { listRef: { current: e8 }, index: r20 } };
  })(), [ee4, te6] = Re(K4), oe3 = be(), R9 = y(P7, t12 ? ee4 : null, o21.actions.setOptionsElement, S10), z3 = p();
  (0, import_react105.useEffect)(() => {
    var D8;
    let e8 = v5;
    e8 && d14 === R7.Open && e8 !== ((D8 = o2(e8)) == null ? void 0 : D8.activeElement) && (e8 == null || e8.focus({ preventScroll: true }));
  }, [d14, v5]);
  let xe3 = o5((e8) => {
    var D8, X6;
    switch (z3.dispose(), e8.key) {
      case o9.Space:
        if (o21.state.searchQuery !== "") return e8.preventDefault(), e8.stopPropagation(), o21.actions.search(e8.key);
      case o9.Enter:
        if (e8.preventDefault(), e8.stopPropagation(), o21.state.activeOptionIndex !== null) {
          let { dataRef: ne4 } = o21.state.options[o21.state.activeOptionIndex];
          o21.actions.onChange(ne4.current.value);
        }
        a25.mode === A6.Single && ((0, import_react_dom9.flushSync)(() => o21.actions.closeListbox()), (D8 = o21.state.buttonElement) == null || D8.focus({ preventScroll: true }));
        break;
      case u(a25.orientation, { vertical: o9.ArrowDown, horizontal: o9.ArrowRight }):
        return e8.preventDefault(), e8.stopPropagation(), o21.actions.goToOption({ focus: c10.Next });
      case u(a25.orientation, { vertical: o9.ArrowUp, horizontal: o9.ArrowLeft }):
        return e8.preventDefault(), e8.stopPropagation(), o21.actions.goToOption({ focus: c10.Previous });
      case o9.Home:
      case o9.PageUp:
        return e8.preventDefault(), e8.stopPropagation(), o21.actions.goToOption({ focus: c10.First });
      case o9.End:
      case o9.PageDown:
        return e8.preventDefault(), e8.stopPropagation(), o21.actions.goToOption({ focus: c10.Last });
      case o9.Escape:
        e8.preventDefault(), e8.stopPropagation(), (0, import_react_dom9.flushSync)(() => o21.actions.closeListbox()), (X6 = o21.state.buttonElement) == null || X6.focus({ preventScroll: true });
        return;
      case o9.Tab:
        e8.preventDefault(), e8.stopPropagation(), (0, import_react_dom9.flushSync)(() => o21.actions.closeListbox()), j3(o21.state.buttonElement, e8.shiftKey ? F2.Previous : F2.Next);
        break;
      default:
        e8.key.length === 1 && (o21.actions.search(e8.key), z3.setTimeout(() => o21.actions.clearSearch(), 350));
        break;
    }
  }), Oe5 = S6(o21, (e8) => {
    var D8;
    return (D8 = e8.buttonElement) == null ? void 0 : D8.id;
  }), Le5 = (0, import_react105.useMemo)(() => ({ open: d14 === R7.Open }), [d14]), Pe4 = _(t12 ? oe3() : {}, { id: p6, ref: R9, "aria-activedescendant": S6(o21, o21.selectors.activeDescendantId), "aria-multiselectable": a25.mode === A6.Multi ? true : void 0, "aria-labelledby": Oe5, "aria-orientation": a25.orientation, onKeyDown: xe3, role: "listbox", tabIndex: d14 === R7.Open ? 0 : void 0, style: { ...T12.style, ...te6, "--button-width": d3(O9, true).width }, ...R4(B5) }), ge6 = L(), Ee3 = (0, import_react105.useMemo)(() => a25.mode === A6.Multi ? a25 : { ...a25, isSelected: V6 }, [a25, V6]);
  return import_react105.default.createElement(oe, { enabled: i19 ? x11.static || h11 : false, ownerDocument: b11 }, import_react105.default.createElement(Z2.Provider, { value: Ee3 }, ge6({ ourProps: Pe4, theirProps: T12, slot: Le5, defaultTag: gt, features: Et, visible: _8, name: "Listbox.Options" })));
}
var ht = "div";
function Dt3(x11, P7) {
  let g8 = (0, import_react50.useId)(), { id: p6 = `headlessui-listbox-option-${g8}`, disabled: l17 = false, value: i19, ...E15 } = x11, s15 = (0, import_react105.useContext)(ye4) === true, T12 = q3("Listbox.Option"), t12 = l14("Listbox.Option"), u19 = S6(t12, (n13) => t12.selectors.isActive(n13, p6)), S10 = T12.isSelected(i19), a25 = (0, import_react105.useRef)(null), o21 = s12(a25), d14 = s3({ disabled: l17, value: i19, domRef: a25, get textValue() {
    return o21();
  } }), O9 = y(P7, a25, (n13) => {
    n13 ? T12.listRef.current.set(p6, n13) : T12.listRef.current.delete(p6);
  }), v5 = S6(t12, (n13) => t12.selectors.shouldScrollIntoView(n13, p6));
  n(() => {
    if (v5) return o3().requestAnimationFrame(() => {
      var n13, _8;
      (_8 = (n13 = a25.current) == null ? void 0 : n13.scrollIntoView) == null || _8.call(n13, { block: "nearest" });
    });
  }, [v5, a25]), n(() => {
    if (!s15) return t12.actions.registerOption(p6, d14), () => t12.actions.unregisterOption(p6);
  }, [d14, p6, s15]);
  let A8 = o5((n13) => {
    var _8;
    if (l17) return n13.preventDefault();
    t12.actions.onChange(i19), T12.mode === A6.Single && ((0, import_react_dom9.flushSync)(() => t12.actions.closeListbox()), (_8 = t12.state.buttonElement) == null || _8.focus({ preventScroll: true }));
  }), b11 = o5(() => {
    if (l17) return t12.actions.goToOption({ focus: c10.Nothing });
    t12.actions.goToOption({ focus: c10.Specific, id: p6 });
  }), m11 = u10(), y8 = o5((n13) => {
    m11.update(n13), !l17 && (u19 || t12.actions.goToOption({ focus: c10.Specific, id: p6 }, E11.Pointer));
  }), h11 = o5((n13) => {
    m11.wasMoved(n13) && (l17 || u19 || t12.actions.goToOption({ focus: c10.Specific, id: p6 }, E11.Pointer));
  }), B5 = o5((n13) => {
    m11.wasMoved(n13) && (l17 || u19 && t12.actions.goToOption({ focus: c10.Nothing }));
  }), I7 = (0, import_react105.useMemo)(() => ({ active: u19, focus: u19, selected: S10, disabled: l17, selectedOption: S10 && s15 }), [u19, S10, l17, s15]), U7 = s15 ? {} : { id: p6, ref: O9, role: "option", tabIndex: l17 === true ? void 0 : -1, "aria-disabled": l17 === true ? true : void 0, "aria-selected": S10, disabled: void 0, onClick: A8, onFocus: b11, onPointerEnter: y8, onMouseEnter: y8, onPointerMove: h11, onMouseMove: h11, onPointerLeave: B5, onMouseLeave: B5 }, k5 = L();
  return !S10 && s15 ? null : k5({ ourProps: U7, theirProps: E15, slot: I7, defaultTag: ht, name: "Listbox.Option" });
}
var St = import_react105.Fragment;
function At(x11, P7) {
  let { options: g8, placeholder: p6, ...l17 } = x11, E15 = { ref: y(P7) }, s15 = q3("ListboxSelectedOption"), T12 = (0, import_react105.useMemo)(() => ({}), []), t12 = s15.value === void 0 || s15.value === null || s15.mode === A6.Multi && Array.isArray(s15.value) && s15.value.length === 0, u19 = L();
  return import_react105.default.createElement(ye4.Provider, { value: true }, u19({ ourProps: E15, theirProps: { ...l17, children: import_react105.default.createElement(import_react105.default.Fragment, null, p6 && t12 ? p6 : g8) }, slot: T12, defaultTag: St, name: "ListboxSelectedOption" }));
}
var _t = K(Ot);
var Rt = K(Pt2);
var Ft = Q;
var Ct = K(vt);
var Mt = K(Dt3);
var wt = K(At);
var Ao2 = Object.assign(_t, { Button: Rt, Label: Ft, Options: Ct, Option: Mt, SelectedOption: wt });

// node_modules/@headlessui/react/dist/components/menu/menu.js
var import_react107 = __toESM(require_react(), 1);
var import_react_dom10 = __toESM(require_react_dom(), 1);

// node_modules/@headlessui/react/dist/components/menu/menu-machine.js
var h9 = Object.defineProperty;
var y7 = (e8, n13, t12) => n13 in e8 ? h9(e8, n13, { enumerable: true, configurable: true, writable: true, value: t12 }) : e8[n13] = t12;
var v4 = (e8, n13, t12) => (y7(e8, typeof n13 != "symbol" ? n13 + "" : n13, t12), t12);
var M10 = ((t12) => (t12[t12.Open = 0] = "Open", t12[t12.Closed = 1] = "Closed", t12))(M10 || {});
var T11 = ((t12) => (t12[t12.Pointer = 0] = "Pointer", t12[t12.Other = 1] = "Other", t12))(T11 || {});
var b9 = ((i19) => (i19[i19.OpenMenu = 0] = "OpenMenu", i19[i19.CloseMenu = 1] = "CloseMenu", i19[i19.GoToItem = 2] = "GoToItem", i19[i19.Search = 3] = "Search", i19[i19.ClearSearch = 4] = "ClearSearch", i19[i19.RegisterItems = 5] = "RegisterItems", i19[i19.UnregisterItems = 6] = "UnregisterItems", i19[i19.SetButtonElement = 7] = "SetButtonElement", i19[i19.SetItemsElement = 8] = "SetItemsElement", i19[i19.SortItems = 9] = "SortItems", i19))(b9 || {});
function S9(e8, n13 = (t12) => t12) {
  let t12 = e8.activeItemIndex !== null ? e8.items[e8.activeItemIndex] : null, r20 = _3(n13(e8.items.slice()), (u19) => u19.dataRef.current.domRef.current), l17 = t12 ? r20.indexOf(t12) : null;
  return l17 === -1 && (l17 = null), { items: r20, activeItemIndex: l17 };
}
var F7 = { [1](e8) {
  return e8.menuState === 1 ? e8 : { ...e8, activeItemIndex: null, pendingFocus: { focus: c10.Nothing }, menuState: 1 };
}, [0](e8, n13) {
  return e8.menuState === 0 ? e8 : { ...e8, __demoMode: false, pendingFocus: n13.focus, menuState: 0 };
}, [2]: (e8, n13) => {
  var u19, m11, d14, a25, I7;
  if (e8.menuState === 1) return e8;
  let t12 = { ...e8, searchQuery: "", activationTrigger: (u19 = n13.trigger) != null ? u19 : 1, __demoMode: false };
  if (n13.focus === c10.Nothing) return { ...t12, activeItemIndex: null };
  if (n13.focus === c10.Specific) return { ...t12, activeItemIndex: e8.items.findIndex((i19) => i19.id === n13.id) };
  if (n13.focus === c10.Previous) {
    let i19 = e8.activeItemIndex;
    if (i19 !== null) {
      let g8 = e8.items[i19].dataRef.current.domRef, o21 = f15(n13, { resolveItems: () => e8.items, resolveActiveIndex: () => e8.activeItemIndex, resolveId: (s15) => s15.id, resolveDisabled: (s15) => s15.dataRef.current.disabled });
      if (o21 !== null) {
        let s15 = e8.items[o21].dataRef.current.domRef;
        if (((m11 = g8.current) == null ? void 0 : m11.previousElementSibling) === s15.current || ((d14 = s15.current) == null ? void 0 : d14.previousElementSibling) === null) return { ...t12, activeItemIndex: o21 };
      }
    }
  } else if (n13.focus === c10.Next) {
    let i19 = e8.activeItemIndex;
    if (i19 !== null) {
      let g8 = e8.items[i19].dataRef.current.domRef, o21 = f15(n13, { resolveItems: () => e8.items, resolveActiveIndex: () => e8.activeItemIndex, resolveId: (s15) => s15.id, resolveDisabled: (s15) => s15.dataRef.current.disabled });
      if (o21 !== null) {
        let s15 = e8.items[o21].dataRef.current.domRef;
        if (((a25 = g8.current) == null ? void 0 : a25.nextElementSibling) === s15.current || ((I7 = s15.current) == null ? void 0 : I7.nextElementSibling) === null) return { ...t12, activeItemIndex: o21 };
      }
    }
  }
  let r20 = S9(e8), l17 = f15(n13, { resolveItems: () => r20.items, resolveActiveIndex: () => r20.activeItemIndex, resolveId: (i19) => i19.id, resolveDisabled: (i19) => i19.dataRef.current.disabled });
  return { ...t12, ...r20, activeItemIndex: l17 };
}, [3]: (e8, n13) => {
  let r20 = e8.searchQuery !== "" ? 0 : 1, l17 = e8.searchQuery + n13.value.toLowerCase(), m11 = (e8.activeItemIndex !== null ? e8.items.slice(e8.activeItemIndex + r20).concat(e8.items.slice(0, e8.activeItemIndex + r20)) : e8.items).find((a25) => {
    var I7;
    return ((I7 = a25.dataRef.current.textValue) == null ? void 0 : I7.startsWith(l17)) && !a25.dataRef.current.disabled;
  }), d14 = m11 ? e8.items.indexOf(m11) : -1;
  return d14 === -1 || d14 === e8.activeItemIndex ? { ...e8, searchQuery: l17 } : { ...e8, searchQuery: l17, activeItemIndex: d14, activationTrigger: 1 };
}, [4](e8) {
  return e8.searchQuery === "" ? e8 : { ...e8, searchQuery: "", searchActiveItemIndex: null };
}, [5]: (e8, n13) => {
  let t12 = e8.items.concat(n13.items.map((l17) => l17)), r20 = e8.activeItemIndex;
  return e8.pendingFocus.focus !== c10.Nothing && (r20 = f15(e8.pendingFocus, { resolveItems: () => t12, resolveActiveIndex: () => e8.activeItemIndex, resolveId: (l17) => l17.id, resolveDisabled: (l17) => l17.dataRef.current.disabled })), { ...e8, items: t12, activeItemIndex: r20, pendingFocus: { focus: c10.Nothing }, pendingShouldSort: true };
}, [6]: (e8, n13) => {
  let t12 = e8.items, r20 = [], l17 = new Set(n13.items);
  for (let [u19, m11] of t12.entries()) if (l17.has(m11.id) && (r20.push(u19), l17.delete(m11.id), l17.size === 0)) break;
  if (r20.length > 0) {
    t12 = t12.slice();
    for (let u19 of r20.reverse()) t12.splice(u19, 1);
  }
  return { ...e8, items: t12, activationTrigger: 1 };
}, [7]: (e8, n13) => e8.buttonElement === n13.element ? e8 : { ...e8, buttonElement: n13.element }, [8]: (e8, n13) => e8.itemsElement === n13.element ? e8 : { ...e8, itemsElement: n13.element }, [9]: (e8) => e8.pendingShouldSort ? { ...e8, ...S9(e8), pendingShouldSort: false } : e8 };
var x9 = class _x extends m9 {
  constructor(t12) {
    super(t12);
    v4(this, "actions", { registerItem: g2(() => {
      let t13 = [], r20 = /* @__PURE__ */ new Set();
      return [(l17, u19) => {
        r20.has(u19) || (r20.add(u19), t13.push({ id: l17, dataRef: u19 }));
      }, () => (r20.clear(), this.send({ type: 5, items: t13.splice(0) }))];
    }), unregisterItem: g2(() => {
      let t13 = [];
      return [(r20) => t13.push(r20), () => this.send({ type: 6, items: t13.splice(0) })];
    }) });
    v4(this, "selectors", { activeDescendantId(t13) {
      var u19;
      let r20 = t13.activeItemIndex, l17 = t13.items;
      return r20 === null || (u19 = l17[r20]) == null ? void 0 : u19.id;
    }, isActive(t13, r20) {
      var m11;
      let l17 = t13.activeItemIndex, u19 = t13.items;
      return l17 !== null ? ((m11 = u19[l17]) == null ? void 0 : m11.id) === r20 : false;
    }, shouldScrollIntoView(t13, r20) {
      return t13.__demoMode || t13.menuState !== 0 || t13.activationTrigger === 0 ? false : this.isActive(t13, r20);
    } });
    this.on(5, () => {
      requestAnimationFrame(() => {
        this.send({ type: 9 });
      });
    });
  }
  static new({ __demoMode: t12 = false } = {}) {
    return new _x({ __demoMode: t12, menuState: t12 ? 0 : 1, buttonElement: null, itemsElement: null, items: [], searchQuery: "", activeItemIndex: null, activationTrigger: 1, pendingShouldSort: false, pendingFocus: { focus: c10.Nothing } });
  }
  reduce(t12, r20) {
    return u(r20.type, F7, t12, r20);
  }
};

// node_modules/@headlessui/react/dist/components/menu/menu-machine-glue.js
var import_react106 = __toESM(require_react(), 1);
var a23 = (0, import_react106.createContext)(null);
function l15(e8) {
  let n13 = (0, import_react106.useContext)(a23);
  if (n13 === null) {
    let t12 = new Error(`<${e8} /> is missing a parent <Menu /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(t12, i17), t12;
  }
  return n13;
}
function i17({ __demoMode: e8 = false } = {}) {
  return (0, import_react106.useMemo)(() => x9.new({ __demoMode: e8 }), []);
}

// node_modules/@headlessui/react/dist/components/menu/menu.js
var ze2 = import_react107.Fragment;
function Qe(T12, E15) {
  let { __demoMode: i19 = false, ...a25 } = T12, n13 = i17({ __demoMode: i19 }), [s15, o21, P7] = S6(n13, (p6) => [p6.menuState, p6.itemsElement, p6.buttonElement]), c19 = y(E15), _8 = s15 === M10.Open;
  R3(_8, [P7, o21], (p6, F9) => {
    var A8;
    n13.send({ type: b9.CloseMenu }), A2(F9, h5.Loose) || (p6.preventDefault(), (A8 = n13.state.buttonElement) == null || A8.focus());
  });
  let t12 = o5(() => {
    n13.send({ type: b9.CloseMenu });
  }), R9 = (0, import_react107.useMemo)(() => ({ open: s15 === M10.Open, close: t12 }), [s15, t12]), I7 = { ref: c19 }, g8 = L();
  return import_react107.default.createElement(Me, null, import_react107.default.createElement(a23.Provider, { value: n13 }, import_react107.default.createElement(c8, { value: u(s15, { [M10.Open]: i11.Open, [M10.Closed]: i11.Closed }) }, g8({ ourProps: I7, theirProps: a25, slot: R9, defaultTag: ze2, name: "Menu" }))));
}
var Ye2 = "button";
function Ze(T12, E15) {
  let i19 = l15("Menu.Button"), a25 = (0, import_react50.useId)(), { id: n13 = `headlessui-menu-button-${a25}`, disabled: s15 = false, autoFocus: o21 = false, ...P7 } = T12, c19 = (0, import_react107.useRef)(null), _8 = Fe2(), t12 = y(E15, c19, ye(), o5((l17) => i19.send({ type: b9.SetButtonElement, element: l17 }))), R9 = o5((l17) => {
    switch (l17.key) {
      case o9.Space:
      case o9.Enter:
      case o9.ArrowDown:
        l17.preventDefault(), l17.stopPropagation(), i19.send({ type: b9.OpenMenu, focus: { focus: c10.First } });
        break;
      case o9.ArrowUp:
        l17.preventDefault(), l17.stopPropagation(), i19.send({ type: b9.OpenMenu, focus: { focus: c10.Last } });
        break;
    }
  }), I7 = o5((l17) => {
    switch (l17.key) {
      case o9.Space:
        l17.preventDefault();
        break;
    }
  }), [g8, p6] = S6(i19, (l17) => [l17.menuState, l17.itemsElement]), F9 = o5((l17) => {
    var H14;
    if (l17.button === 0) {
      if (r4(l17.currentTarget)) return l17.preventDefault();
      s15 || (g8 === M10.Open ? ((0, import_react_dom10.flushSync)(() => i19.send({ type: b9.CloseMenu })), (H14 = c19.current) == null || H14.focus({ preventScroll: true })) : (l17.preventDefault(), i19.send({ type: b9.OpenMenu, focus: { focus: c10.Nothing }, trigger: T11.Pointer })));
    }
  }), { isFocusVisible: A8, focusProps: f22 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: o21 }), { isHovered: M11, hoverProps: L7 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: s15 }), { pressed: S10, pressProps: O9 } = w({ disabled: s15 }), x11 = (0, import_react107.useMemo)(() => ({ open: g8 === M10.Open, active: S10 || g8 === M10.Open, disabled: s15, hover: M11, focus: A8, autofocus: o21 }), [g8, M11, A8, S10, s15, o21]), U7 = _(_8(), { ref: t12, id: n13, type: e6(T12, c19.current), "aria-haspopup": "menu", "aria-controls": p6 == null ? void 0 : p6.id, "aria-expanded": g8 === M10.Open, disabled: s15 || void 0, autoFocus: o21, onKeyDown: R9, onKeyUp: I7, onMouseDown: F9 }, f22, L7, O9);
  return L()({ ourProps: U7, theirProps: P7, slot: x11, defaultTag: Ye2, name: "Menu.Button" });
}
var et = "div";
var tt = O.RenderStrategy | O.Static;
function ot(T12, E15) {
  let i19 = (0, import_react50.useId)(), { id: a25 = `headlessui-menu-items-${i19}`, anchor: n13, portal: s15 = false, modal: o21 = true, transition: P7 = false, ...c19 } = T12, _8 = xe(n13), t12 = l15("Menu.Items"), [R9, I7] = Re(_8), g8 = be(), [p6, F9] = (0, import_react107.useState)(null), A8 = y(E15, _8 ? R9 : null, o5((e8) => t12.send({ type: b9.SetItemsElement, element: e8 })), F9), [f22, M11] = S6(t12, (e8) => [e8.menuState, e8.buttonElement]), L7 = n9(M11), S10 = n9(p6);
  _8 && (s15 = true);
  let O9 = u12(), [x11, U7] = x3(P7, p6, O9 !== null ? (O9 & i11.Open) === i11.Open : f22 === M10.Open);
  m6(x11, M11, () => {
    t12.send({ type: b9.CloseMenu });
  });
  let G8 = S6(t12, (e8) => e8.__demoMode), l17 = G8 ? false : o21 && f22 === M10.Open;
  f11(l17, S10);
  let H14 = G8 ? false : o21 && f22 === M10.Open;
  y3(H14, { allowed: (0, import_react107.useCallback)(() => [M11, p6], [M11, p6]) });
  let u19 = f22 !== M10.Open, ae5 = s11(u19, M11) ? false : x11;
  (0, import_react107.useEffect)(() => {
    let e8 = p6;
    e8 && f22 === M10.Open && e8 !== (S10 == null ? void 0 : S10.activeElement) && e8.focus({ preventScroll: true });
  }, [f22, p6, S10]), F3(f22 === M10.Open, { container: p6, accept(e8) {
    return e8.getAttribute("role") === "menuitem" ? NodeFilter.FILTER_REJECT : e8.hasAttribute("role") ? NodeFilter.FILTER_SKIP : NodeFilter.FILTER_ACCEPT;
  }, walk(e8) {
    e8.setAttribute("role", "none");
  } });
  let q5 = p(), se2 = o5((e8) => {
    var N3, z3, Q4;
    switch (q5.dispose(), e8.key) {
      case o9.Space:
        if (t12.state.searchQuery !== "") return e8.preventDefault(), e8.stopPropagation(), t12.send({ type: b9.Search, value: e8.key });
      case o9.Enter:
        if (e8.preventDefault(), e8.stopPropagation(), t12.state.activeItemIndex !== null) {
          let { dataRef: de8 } = t12.state.items[t12.state.activeItemIndex];
          (z3 = (N3 = de8.current) == null ? void 0 : N3.domRef.current) == null || z3.click();
        }
        t12.send({ type: b9.CloseMenu }), G2(t12.state.buttonElement);
        break;
      case o9.ArrowDown:
        return e8.preventDefault(), e8.stopPropagation(), t12.send({ type: b9.GoToItem, focus: c10.Next });
      case o9.ArrowUp:
        return e8.preventDefault(), e8.stopPropagation(), t12.send({ type: b9.GoToItem, focus: c10.Previous });
      case o9.Home:
      case o9.PageUp:
        return e8.preventDefault(), e8.stopPropagation(), t12.send({ type: b9.GoToItem, focus: c10.First });
      case o9.End:
      case o9.PageDown:
        return e8.preventDefault(), e8.stopPropagation(), t12.send({ type: b9.GoToItem, focus: c10.Last });
      case o9.Escape:
        e8.preventDefault(), e8.stopPropagation(), (0, import_react_dom10.flushSync)(() => t12.send({ type: b9.CloseMenu })), (Q4 = t12.state.buttonElement) == null || Q4.focus({ preventScroll: true });
        break;
      case o9.Tab:
        e8.preventDefault(), e8.stopPropagation(), (0, import_react_dom10.flushSync)(() => t12.send({ type: b9.CloseMenu })), j3(t12.state.buttonElement, e8.shiftKey ? F2.Previous : F2.Next);
        break;
      default:
        e8.key.length === 1 && (t12.send({ type: b9.Search, value: e8.key }), q5.setTimeout(() => t12.send({ type: b9.ClearSearch }), 350));
        break;
    }
  }), le5 = o5((e8) => {
    switch (e8.key) {
      case o9.Space:
        e8.preventDefault();
        break;
    }
  }), pe5 = (0, import_react107.useMemo)(() => ({ open: f22 === M10.Open }), [f22]), ie4 = _(_8 ? g8() : {}, { "aria-activedescendant": S6(t12, t12.selectors.activeDescendantId), "aria-labelledby": S6(t12, (e8) => {
    var N3;
    return (N3 = e8.buttonElement) == null ? void 0 : N3.id;
  }), id: a25, onKeyDown: se2, onKeyUp: le5, role: "menu", tabIndex: f22 === M10.Open ? 0 : void 0, ref: A8, style: { ...c19.style, ...I7, "--button-width": d3(M11, true).width }, ...R4(U7) }), ue5 = L();
  return import_react107.default.createElement(oe, { enabled: s15 ? T12.static || x11 : false, ownerDocument: L7 }, ue5({ ourProps: ie4, theirProps: c19, slot: pe5, defaultTag: et, features: tt, visible: ae5, name: "Menu.Items" }));
}
var nt = import_react107.Fragment;
function rt(T12, E15) {
  let i19 = (0, import_react50.useId)(), { id: a25 = `headlessui-menu-item-${i19}`, disabled: n13 = false, ...s15 } = T12, o21 = l15("Menu.Item"), P7 = S6(o21, (u19) => o21.selectors.isActive(u19, a25)), c19 = (0, import_react107.useRef)(null), _8 = y(E15, c19), t12 = S6(o21, (u19) => o21.selectors.shouldScrollIntoView(u19, a25));
  n(() => {
    if (t12) return o3().requestAnimationFrame(() => {
      var u19, J7;
      (J7 = (u19 = c19.current) == null ? void 0 : u19.scrollIntoView) == null || J7.call(u19, { block: "nearest" });
    });
  }, [t12, c19]);
  let R9 = s12(c19), I7 = (0, import_react107.useRef)({ disabled: n13, domRef: c19, get textValue() {
    return R9();
  } });
  n(() => {
    I7.current.disabled = n13;
  }, [I7, n13]), n(() => (o21.actions.registerItem(a25, I7), () => o21.actions.unregisterItem(a25)), [I7, a25]);
  let g8 = o5(() => {
    o21.send({ type: b9.CloseMenu });
  }), p6 = o5((u19) => {
    if (n13) return u19.preventDefault();
    o21.send({ type: b9.CloseMenu }), G2(o21.state.buttonElement);
  }), F9 = o5(() => {
    if (n13) return o21.send({ type: b9.GoToItem, focus: c10.Nothing });
    o21.send({ type: b9.GoToItem, focus: c10.Specific, id: a25 });
  }), A8 = u10(), f22 = o5((u19) => {
    A8.update(u19), !n13 && (P7 || o21.send({ type: b9.GoToItem, focus: c10.Specific, id: a25, trigger: T11.Pointer }));
  }), M11 = o5((u19) => {
    A8.wasMoved(u19) && (n13 || P7 || o21.send({ type: b9.GoToItem, focus: c10.Specific, id: a25, trigger: T11.Pointer }));
  }), L7 = o5((u19) => {
    A8.wasMoved(u19) && (n13 || P7 && o21.send({ type: b9.GoToItem, focus: c10.Nothing }));
  }), [S10, O9] = K2(), [x11, U7] = w3(), G8 = (0, import_react107.useMemo)(() => ({ active: P7, focus: P7, disabled: n13, close: g8 }), [P7, n13, g8]), l17 = { id: a25, ref: _8, role: "menuitem", tabIndex: n13 === true ? void 0 : -1, "aria-disabled": n13 === true ? true : void 0, "aria-labelledby": S10, "aria-describedby": x11, disabled: void 0, onClick: p6, onFocus: F9, onPointerEnter: f22, onMouseEnter: f22, onPointerMove: M11, onMouseMove: M11, onPointerLeave: L7, onMouseLeave: L7 }, H14 = L();
  return import_react107.default.createElement(O9, null, import_react107.default.createElement(U7, null, H14({ ourProps: l17, theirProps: s15, slot: G8, defaultTag: nt, name: "Menu.Item" })));
}
var at = "div";
function st(T12, E15) {
  let [i19, a25] = K2(), n13 = T12, s15 = { ref: E15, "aria-labelledby": i19, role: "group" }, o21 = L();
  return import_react107.default.createElement(a25, null, o21({ ourProps: s15, theirProps: n13, slot: {}, defaultTag: at, name: "Menu.Section" }));
}
var lt = "header";
function pt(T12, E15) {
  let i19 = (0, import_react50.useId)(), { id: a25 = `headlessui-menu-heading-${i19}`, ...n13 } = T12, s15 = P5();
  n(() => s15.register(a25), [a25, s15.register]);
  let o21 = { id: a25, ref: E15, role: "presentation", ...s15.props };
  return L()({ ourProps: o21, theirProps: n13, slot: {}, defaultTag: lt, name: "Menu.Heading" });
}
var it = "div";
function ut(T12, E15) {
  let i19 = T12, a25 = { ref: E15, role: "separator" };
  return L()({ ourProps: a25, theirProps: i19, slot: {}, defaultTag: it, name: "Menu.Separator" });
}
var dt = K(Qe);
var mt = K(Ze);
var Tt = K(ot);
var ft = K(rt);
var ct = K(st);
var yt2 = K(pt);
var Et2 = K(ut);
var to = Object.assign(dt, { Button: mt, Items: Tt, Item: ft, Section: ct, Heading: yt2, Separator: Et2 });

// node_modules/@headlessui/react/dist/components/popover/popover.js
var import_react108 = __toESM(require_react(), 1);
var at2 = ((P7) => (P7[P7.Open = 0] = "Open", P7[P7.Closed = 1] = "Closed", P7))(at2 || {});
var pt2 = ((s15) => (s15[s15.TogglePopover = 0] = "TogglePopover", s15[s15.ClosePopover = 1] = "ClosePopover", s15[s15.SetButton = 2] = "SetButton", s15[s15.SetButtonId = 3] = "SetButtonId", s15[s15.SetPanel = 4] = "SetPanel", s15[s15.SetPanelId = 5] = "SetPanelId", s15))(pt2 || {});
var st2 = { [0]: (t12) => ({ ...t12, popoverState: u(t12.popoverState, { [0]: 1, [1]: 0 }), __demoMode: false }), [1](t12) {
  return t12.popoverState === 1 ? t12 : { ...t12, popoverState: 1, __demoMode: false };
}, [2](t12, l17) {
  return t12.button === l17.button ? t12 : { ...t12, button: l17.button };
}, [3](t12, l17) {
  return t12.buttonId === l17.buttonId ? t12 : { ...t12, buttonId: l17.buttonId };
}, [4](t12, l17) {
  return t12.panel === l17.panel ? t12 : { ...t12, panel: l17.panel };
}, [5](t12, l17) {
  return t12.panelId === l17.panelId ? t12 : { ...t12, panelId: l17.panelId };
} };
var be3 = (0, import_react108.createContext)(null);
be3.displayName = "PopoverContext";
function ie3(t12) {
  let l17 = (0, import_react108.useContext)(be3);
  if (l17 === null) {
    let P7 = new Error(`<${t12} /> is missing a parent <Popover /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(P7, ie3), P7;
  }
  return l17;
}
var de7 = (0, import_react108.createContext)(null);
de7.displayName = "PopoverAPIContext";
function ge3(t12) {
  let l17 = (0, import_react108.useContext)(de7);
  if (l17 === null) {
    let P7 = new Error(`<${t12} /> is missing a parent <Popover /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(P7, ge3), P7;
  }
  return l17;
}
var Se5 = (0, import_react108.createContext)(null);
Se5.displayName = "PopoverGroupContext";
function Oe3() {
  return (0, import_react108.useContext)(Se5);
}
var Pe3 = (0, import_react108.createContext)(null);
Pe3.displayName = "PopoverPanelContext";
function ut2() {
  return (0, import_react108.useContext)(Pe3);
}
function it2(t12, l17) {
  return u(l17.type, st2, t12, l17);
}
var dt2 = "div";
function Pt3(t12, l17) {
  var q5;
  let { __demoMode: P7 = false, ...C10 } = t12, m11 = (0, import_react108.useRef)(null), A8 = y(l17, T2((a25) => {
    m11.current = a25;
  })), s15 = (0, import_react108.useRef)([]), n13 = (0, import_react108.useReducer)(it2, { __demoMode: P7, popoverState: P7 ? 0 : 1, buttons: s15, button: null, buttonId: null, panel: null, panelId: null, beforePanelSentinel: (0, import_react108.createRef)(), afterPanelSentinel: (0, import_react108.createRef)(), afterButtonSentinel: (0, import_react108.createRef)() }), [{ popoverState: v5, button: i19, buttonId: o21, panel: u19, panelId: R9, beforePanelSentinel: y8, afterPanelSentinel: h11, afterButtonSentinel: d14 }, r20] = n13, T12 = n9((q5 = m11.current) != null ? q5 : i19), g8 = (0, import_react108.useMemo)(() => {
    if (!i19 || !u19) return false;
    for (let S10 of document.querySelectorAll("body > *")) if (Number(S10 == null ? void 0 : S10.contains(i19)) ^ Number(S10 == null ? void 0 : S10.contains(u19))) return true;
    let a25 = b2(), e8 = a25.indexOf(i19), p6 = (e8 + a25.length - 1) % a25.length, f22 = (e8 + 1) % a25.length, c19 = a25[p6], O9 = a25[f22];
    return !u19.contains(c19) && !u19.contains(O9);
  }, [i19, u19]), _8 = s3(o21), L7 = s3(R9), I7 = (0, import_react108.useMemo)(() => ({ buttonId: _8, panelId: L7, close: () => r20({ type: 1 }) }), [_8, L7, r20]), M11 = Oe3(), k5 = M11 == null ? void 0 : M11.registerPopover, V6 = o5(() => {
    var a25;
    return (a25 = M11 == null ? void 0 : M11.isFocusWithinPopoverGroup()) != null ? a25 : (T12 == null ? void 0 : T12.activeElement) && ((i19 == null ? void 0 : i19.contains(T12.activeElement)) || (u19 == null ? void 0 : u19.contains(T12.activeElement)));
  });
  (0, import_react108.useEffect)(() => k5 == null ? void 0 : k5(I7), [k5, I7]);
  let [B5, U7] = le(), F9 = b6(i19), N3 = R6({ mainTreeNode: F9, portals: B5, defaultContainers: [i19, u19] });
  E5(T12 == null ? void 0 : T12.defaultView, "focus", (a25) => {
    var e8, p6, f22, c19, O9, S10;
    a25.target !== window && a25.target instanceof HTMLElement && v5 === 0 && (V6() || i19 && u19 && (N3.contains(a25.target) || (p6 = (e8 = y8.current) == null ? void 0 : e8.contains) != null && p6.call(e8, a25.target) || (c19 = (f22 = h11.current) == null ? void 0 : f22.contains) != null && c19.call(f22, a25.target) || (S10 = (O9 = d14.current) == null ? void 0 : O9.contains) != null && S10.call(O9, a25.target) || r20({ type: 1 })));
  }, true), R3(v5 === 0, N3.resolveContainers, (a25, e8) => {
    r20({ type: 1 }), A2(e8, h5.Loose) || (a25.preventDefault(), i19 == null || i19.focus());
  });
  let x11 = o5((a25) => {
    r20({ type: 1 });
    let e8 = (() => a25 ? a25 instanceof HTMLElement ? a25 : "current" in a25 && a25.current instanceof HTMLElement ? a25.current : i19 : i19)();
    e8 == null || e8.focus();
  }), ee4 = (0, import_react108.useMemo)(() => ({ close: x11, isPortalled: g8 }), [x11, g8]), $4 = (0, import_react108.useMemo)(() => ({ open: v5 === 0, close: x11 }), [v5, x11]), J7 = { ref: A8 }, X6 = L();
  return import_react108.default.createElement(O5, { node: F9 }, import_react108.default.createElement(Me, null, import_react108.default.createElement(Pe3.Provider, { value: null }, import_react108.default.createElement(be3.Provider, { value: n13 }, import_react108.default.createElement(de7.Provider, { value: ee4 }, import_react108.default.createElement(C4, { value: x11 }, import_react108.default.createElement(c8, { value: u(v5, { [0]: i11.Open, [1]: i11.Closed }) }, import_react108.default.createElement(U7, null, X6({ ourProps: J7, theirProps: C10, slot: $4, defaultTag: dt2, name: "Popover" })))))))));
}
var ft2 = "button";
function ct2(t12, l17) {
  let P7 = (0, import_react50.useId)(), { id: C10 = `headlessui-popover-button-${P7}`, disabled: m11 = false, autoFocus: A8 = false, ...s15 } = t12, [n13, v5] = ie3("Popover.Button"), { isPortalled: i19 } = ge3("Popover.Button"), o21 = (0, import_react108.useRef)(null), u19 = `headlessui-focus-sentinel-${(0, import_react50.useId)()}`, R9 = Oe3(), y8 = R9 == null ? void 0 : R9.closeOthers, d14 = ut2() !== null;
  (0, import_react108.useEffect)(() => {
    if (!d14) return v5({ type: 3, buttonId: C10 }), () => {
      v5({ type: 3, buttonId: null });
    };
  }, [d14, C10, v5]);
  let [r20] = (0, import_react108.useState)(() => Symbol()), T12 = y(o21, l17, ye(), o5((e8) => {
    if (!d14) {
      if (e8) n13.buttons.current.push(r20);
      else {
        let p6 = n13.buttons.current.indexOf(r20);
        p6 !== -1 && n13.buttons.current.splice(p6, 1);
      }
      n13.buttons.current.length > 1 && console.warn("You are already using a <Popover.Button /> but only 1 <Popover.Button /> is supported."), e8 && v5({ type: 2, button: e8 });
    }
  })), g8 = y(o21, l17), _8 = n9(o21), L7 = o5((e8) => {
    var p6, f22, c19;
    if (d14) {
      if (n13.popoverState === 1) return;
      switch (e8.key) {
        case o9.Space:
        case o9.Enter:
          e8.preventDefault(), (f22 = (p6 = e8.target).click) == null || f22.call(p6), v5({ type: 1 }), (c19 = n13.button) == null || c19.focus();
          break;
      }
    } else switch (e8.key) {
      case o9.Space:
      case o9.Enter:
        e8.preventDefault(), e8.stopPropagation(), n13.popoverState === 1 && (y8 == null || y8(n13.buttonId)), v5({ type: 0 });
        break;
      case o9.Escape:
        if (n13.popoverState !== 0) return y8 == null ? void 0 : y8(n13.buttonId);
        if (!o21.current || _8 != null && _8.activeElement && !o21.current.contains(_8.activeElement)) return;
        e8.preventDefault(), e8.stopPropagation(), v5({ type: 1 });
        break;
    }
  }), I7 = o5((e8) => {
    d14 || e8.key === o9.Space && e8.preventDefault();
  }), M11 = o5((e8) => {
    var p6, f22;
    r4(e8.currentTarget) || m11 || (d14 ? (v5({ type: 1 }), (p6 = n13.button) == null || p6.focus()) : (e8.preventDefault(), e8.stopPropagation(), n13.popoverState === 1 && (y8 == null || y8(n13.buttonId)), v5({ type: 0 }), (f22 = n13.button) == null || f22.focus()));
  }), k5 = o5((e8) => {
    e8.preventDefault(), e8.stopPropagation();
  }), { isFocusVisible: V6, focusProps: B5 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: A8 }), { isHovered: U7, hoverProps: F9 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: m11 }), { pressed: N3, pressProps: Z4 } = w({ disabled: m11 }), x11 = n13.popoverState === 0, ee4 = (0, import_react108.useMemo)(() => ({ open: x11, active: N3 || x11, disabled: m11, hover: U7, focus: V6, autofocus: A8 }), [x11, U7, V6, N3, m11, A8]), $4 = e6(t12, n13.button), J7 = d14 ? _({ ref: g8, type: $4, onKeyDown: L7, onClick: M11, disabled: m11 || void 0, autoFocus: A8 }, B5, F9, Z4) : _({ ref: T12, id: n13.buttonId, type: $4, "aria-expanded": n13.popoverState === 0, "aria-controls": n13.panel ? n13.panelId : void 0, disabled: m11 || void 0, autoFocus: A8, onKeyDown: L7, onKeyUp: I7, onClick: M11, onMouseDown: k5 }, B5, F9, Z4), X6 = u17(), q5 = o5(() => {
    let e8 = n13.panel;
    if (!e8) return;
    function p6() {
      u(X6.current, { [a18.Forwards]: () => P6(e8, F2.First), [a18.Backwards]: () => P6(e8, F2.Last) }) === T5.Error && P6(b2().filter((c19) => c19.dataset.headlessuiFocusGuard !== "true"), u(X6.current, { [a18.Forwards]: F2.Next, [a18.Backwards]: F2.Previous }), { relativeTo: n13.button });
    }
    p6();
  }), a25 = L();
  return import_react108.default.createElement(import_react108.default.Fragment, null, a25({ ourProps: J7, theirProps: s15, slot: ee4, defaultTag: ft2, name: "Popover.Button" }), x11 && !d14 && i19 && import_react108.default.createElement(f4, { id: u19, ref: n13.afterButtonSentinel, features: s4.Focusable, "data-headlessui-focus-guard": true, as: "button", type: "button", onFocus: q5 }));
}
var vt2 = "div";
var Tt2 = O.RenderStrategy | O.Static;
function Le2(t12, l17) {
  let P7 = (0, import_react50.useId)(), { id: C10 = `headlessui-popover-backdrop-${P7}`, transition: m11 = false, ...A8 } = t12, [{ popoverState: s15 }, n13] = ie3("Popover.Backdrop"), [v5, i19] = (0, import_react108.useState)(null), o21 = y(l17, i19), u19 = u12(), [R9, y8] = x3(m11, v5, u19 !== null ? (u19 & i11.Open) === i11.Open : s15 === 0), h11 = o5((g8) => {
    if (r4(g8.currentTarget)) return g8.preventDefault();
    n13({ type: 1 });
  }), d14 = (0, import_react108.useMemo)(() => ({ open: s15 === 0 }), [s15]), r20 = { ref: o21, id: C10, "aria-hidden": true, onClick: h11, ...R4(y8) };
  return L()({ ourProps: r20, theirProps: A8, slot: d14, defaultTag: vt2, features: Tt2, visible: R9, name: "Popover.Backdrop" });
}
var mt2 = "div";
var yt3 = O.RenderStrategy | O.Static;
function Et3(t12, l17) {
  let P7 = (0, import_react50.useId)(), { id: C10 = `headlessui-popover-panel-${P7}`, focus: m11 = false, anchor: A8, portal: s15 = false, modal: n13 = false, transition: v5 = false, ...i19 } = t12, [o21, u19] = ie3("Popover.Panel"), { close: R9, isPortalled: y8 } = ge3("Popover.Panel"), h11 = `headlessui-focus-sentinel-before-${P7}`, d14 = `headlessui-focus-sentinel-after-${P7}`, r20 = (0, import_react108.useRef)(null), T12 = xe(A8), [g8, _8] = Re(T12), L7 = be();
  T12 && (s15 = true);
  let [I7, M11] = (0, import_react108.useState)(null), k5 = y(r20, l17, T12 ? g8 : null, o5((e8) => u19({ type: 4, panel: e8 })), M11), V6 = n9(o21.button), B5 = n9(r20);
  n(() => (u19({ type: 5, panelId: C10 }), () => {
    u19({ type: 5, panelId: null });
  }), [C10, u19]);
  let U7 = u12(), [F9, N3] = x3(v5, I7, U7 !== null ? (U7 & i11.Open) === i11.Open : o21.popoverState === 0);
  m6(F9, o21.button, () => {
    u19({ type: 1 });
  });
  let Z4 = o21.__demoMode ? false : n13 && F9;
  f11(Z4, B5);
  let x11 = o5((e8) => {
    var p6;
    switch (e8.key) {
      case o9.Escape:
        if (o21.popoverState !== 0 || !r20.current || B5 != null && B5.activeElement && !r20.current.contains(B5.activeElement)) return;
        e8.preventDefault(), e8.stopPropagation(), u19({ type: 1 }), (p6 = o21.button) == null || p6.focus();
        break;
    }
  });
  (0, import_react108.useEffect)(() => {
    var e8;
    t12.static || o21.popoverState === 1 && ((e8 = t12.unmount) == null || e8) && u19({ type: 4, panel: null });
  }, [o21.popoverState, t12.unmount, t12.static, u19]), (0, import_react108.useEffect)(() => {
    if (o21.__demoMode || !m11 || o21.popoverState !== 0 || !r20.current) return;
    let e8 = B5 == null ? void 0 : B5.activeElement;
    r20.current.contains(e8) || P6(r20.current, F2.First);
  }, [o21.__demoMode, m11, r20.current, o21.popoverState]);
  let ee4 = (0, import_react108.useMemo)(() => ({ open: o21.popoverState === 0, close: R9 }), [o21.popoverState, R9]), $4 = _(T12 ? L7() : {}, { ref: k5, id: C10, onKeyDown: x11, onBlur: m11 && o21.popoverState === 0 ? (e8) => {
    var f22, c19, O9, S10, w10;
    let p6 = e8.relatedTarget;
    p6 && r20.current && ((f22 = r20.current) != null && f22.contains(p6) || (u19({ type: 1 }), ((O9 = (c19 = o21.beforePanelSentinel.current) == null ? void 0 : c19.contains) != null && O9.call(c19, p6) || (w10 = (S10 = o21.afterPanelSentinel.current) == null ? void 0 : S10.contains) != null && w10.call(S10, p6)) && p6.focus({ preventScroll: true })));
  } : void 0, tabIndex: -1, style: { ...i19.style, ..._8, "--button-width": d3(o21.button, true).width }, ...R4(N3) }), J7 = u17(), X6 = o5(() => {
    let e8 = r20.current;
    if (!e8) return;
    function p6() {
      u(J7.current, { [a18.Forwards]: () => {
        var c19;
        P6(e8, F2.First) === T5.Error && ((c19 = o21.afterPanelSentinel.current) == null || c19.focus());
      }, [a18.Backwards]: () => {
        var f22;
        (f22 = o21.button) == null || f22.focus({ preventScroll: true });
      } });
    }
    p6();
  }), q5 = o5(() => {
    let e8 = r20.current;
    if (!e8) return;
    function p6() {
      u(J7.current, { [a18.Forwards]: () => {
        if (!o21.button) return;
        let f22 = b2(), c19 = f22.indexOf(o21.button), O9 = f22.slice(0, c19 + 1), w10 = [...f22.slice(c19 + 1), ...O9];
        for (let fe4 of w10.slice()) if (fe4.dataset.headlessuiFocusGuard === "true" || I7 != null && I7.contains(fe4)) {
          let Ae3 = w10.indexOf(fe4);
          Ae3 !== -1 && w10.splice(Ae3, 1);
        }
        P6(w10, F2.First, { sorted: false });
      }, [a18.Backwards]: () => {
        var c19;
        P6(e8, F2.Previous) === T5.Error && ((c19 = o21.button) == null || c19.focus());
      } });
    }
    p6();
  }), a25 = L();
  return import_react108.default.createElement(s7, null, import_react108.default.createElement(Pe3.Provider, { value: C10 }, import_react108.default.createElement(de7.Provider, { value: { close: R9, isPortalled: y8 } }, import_react108.default.createElement(oe, { enabled: s15 ? t12.static || F9 : false, ownerDocument: V6 }, F9 && y8 && import_react108.default.createElement(f4, { id: h11, ref: o21.beforePanelSentinel, features: s4.Focusable, "data-headlessui-focus-guard": true, as: "button", type: "button", onFocus: X6 }), a25({ ourProps: $4, theirProps: i19, slot: ee4, defaultTag: mt2, features: yt3, visible: F9, name: "Popover.Panel" }), F9 && y8 && import_react108.default.createElement(f4, { id: d14, ref: o21.afterPanelSentinel, features: s4.Focusable, "data-headlessui-focus-guard": true, as: "button", type: "button", onFocus: q5 })))));
}
var bt = "div";
function gt2(t12, l17) {
  let P7 = (0, import_react108.useRef)(null), C10 = y(P7, l17), [m11, A8] = (0, import_react108.useState)([]), s15 = o5((d14) => {
    A8((r20) => {
      let T12 = r20.indexOf(d14);
      if (T12 !== -1) {
        let g8 = r20.slice();
        return g8.splice(T12, 1), g8;
      }
      return r20;
    });
  }), n13 = o5((d14) => (A8((r20) => [...r20, d14]), () => s15(d14))), v5 = o5(() => {
    var T12;
    let d14 = o2(P7);
    if (!d14) return false;
    let r20 = d14.activeElement;
    return (T12 = P7.current) != null && T12.contains(r20) ? true : m11.some((g8) => {
      var _8, L7;
      return ((_8 = d14.getElementById(g8.buttonId.current)) == null ? void 0 : _8.contains(r20)) || ((L7 = d14.getElementById(g8.panelId.current)) == null ? void 0 : L7.contains(r20));
    });
  }), i19 = o5((d14) => {
    for (let r20 of m11) r20.buttonId.current !== d14 && r20.close();
  }), o21 = (0, import_react108.useMemo)(() => ({ registerPopover: n13, unregisterPopover: s15, isFocusWithinPopoverGroup: v5, closeOthers: i19 }), [n13, s15, v5, i19]), u19 = (0, import_react108.useMemo)(() => ({}), []), R9 = t12, y8 = { ref: C10 }, h11 = L();
  return import_react108.default.createElement(O5, null, import_react108.default.createElement(Se5.Provider, { value: o21 }, h11({ ourProps: y8, theirProps: R9, slot: u19, defaultTag: bt, name: "Popover.Group" })));
}
var St2 = K(Pt3);
var At2 = K(ct2);
var Ct2 = K(Le2);
var Rt2 = K(Le2);
var Bt = K(Et3);
var _t2 = K(gt2);
var ao = Object.assign(St2, { Button: At2, Backdrop: Rt2, Overlay: Ct2, Panel: Bt, Group: _t2 });

// node_modules/@headlessui/react/dist/components/radio-group/radio-group.js
var import_react109 = __toESM(require_react(), 1);
var Ie3 = ((e8) => (e8[e8.RegisterOption = 0] = "RegisterOption", e8[e8.UnregisterOption = 1] = "UnregisterOption", e8))(Ie3 || {});
var Fe4 = { [0](o21, t12) {
  let e8 = [...o21.options, { id: t12.id, element: t12.element, propsRef: t12.propsRef }];
  return { ...o21, options: _3(e8, (i19) => i19.element.current) };
}, [1](o21, t12) {
  let e8 = o21.options.slice(), i19 = o21.options.findIndex((v5) => v5.id === t12.id);
  return i19 === -1 ? o21 : (e8.splice(i19, 1), { ...o21, options: e8 });
} };
var J5 = (0, import_react109.createContext)(null);
J5.displayName = "RadioGroupDataContext";
function X5(o21) {
  let t12 = (0, import_react109.useContext)(J5);
  if (t12 === null) {
    let e8 = new Error(`<${o21} /> is missing a parent <RadioGroup /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(e8, X5), e8;
  }
  return t12;
}
var z = (0, import_react109.createContext)(null);
z.displayName = "RadioGroupActionsContext";
function q4(o21) {
  let t12 = (0, import_react109.useContext)(z);
  if (t12 === null) {
    let e8 = new Error(`<${o21} /> is missing a parent <RadioGroup /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(e8, q4), e8;
  }
  return t12;
}
function Ue2(o21, t12) {
  return u(t12.type, Fe4, o21, t12);
}
var Me3 = "div";
function Se6(o21, t12) {
  let e8 = (0, import_react50.useId)(), i19 = a3(), { id: v5 = `headlessui-radiogroup-${e8}`, value: m11, form: D8, name: n13, onChange: f22, by: u19, disabled: a25 = i19 || false, defaultValue: M11, tabIndex: T12 = 0, ...S10 } = o21, R9 = u8(u19), [A8, y8] = (0, import_react109.useReducer)(Ue2, { options: [] }), p6 = A8.options, [C10, _8] = K2(), [h11, L7] = w3(), k5 = (0, import_react109.useRef)(null), c19 = y(k5, t12), b11 = l2(M11), [l17, I7] = T(m11, f22, b11), g8 = (0, import_react109.useMemo)(() => p6.find((r20) => !r20.propsRef.current.disabled), [p6]), O9 = (0, import_react109.useMemo)(() => p6.some((r20) => R9(r20.propsRef.current.value, l17)), [p6, l17]), s15 = o5((r20) => {
    var d14;
    if (a25 || R9(r20, l17)) return false;
    let F9 = (d14 = p6.find((w10) => R9(w10.propsRef.current.value, r20))) == null ? void 0 : d14.propsRef.current;
    return F9 != null && F9.disabled ? false : (I7 == null || I7(r20), true);
  }), ue5 = o5((r20) => {
    let F9 = k5.current;
    if (!F9) return;
    let d14 = o2(F9), w10 = p6.filter((P7) => P7.propsRef.current.disabled === false).map((P7) => P7.element.current);
    switch (r20.key) {
      case o9.Enter:
        p2(r20.currentTarget);
        break;
      case o9.ArrowLeft:
      case o9.ArrowUp:
        if (r20.preventDefault(), r20.stopPropagation(), P6(w10, F2.Previous | F2.WrapAround) === T5.Success) {
          let E15 = p6.find((W3) => W3.element.current === (d14 == null ? void 0 : d14.activeElement));
          E15 && s15(E15.propsRef.current.value);
        }
        break;
      case o9.ArrowRight:
      case o9.ArrowDown:
        if (r20.preventDefault(), r20.stopPropagation(), P6(w10, F2.Next | F2.WrapAround) === T5.Success) {
          let E15 = p6.find((W3) => W3.element.current === (d14 == null ? void 0 : d14.activeElement));
          E15 && s15(E15.propsRef.current.value);
        }
        break;
      case o9.Space:
        {
          r20.preventDefault(), r20.stopPropagation();
          let P7 = p6.find((E15) => E15.element.current === (d14 == null ? void 0 : d14.activeElement));
          P7 && s15(P7.propsRef.current.value);
        }
        break;
    }
  }), Q4 = o5((r20) => (y8({ type: 0, ...r20 }), () => y8({ type: 1, id: r20.id }))), ce5 = (0, import_react109.useMemo)(() => ({ value: l17, firstOption: g8, containsCheckedOption: O9, disabled: a25, compare: R9, tabIndex: T12, ...A8 }), [l17, g8, O9, a25, R9, T12, A8]), fe4 = (0, import_react109.useMemo)(() => ({ registerOption: Q4, change: s15 }), [Q4, s15]), Te4 = { ref: c19, id: v5, role: "radiogroup", "aria-labelledby": C10, "aria-describedby": h11, onKeyDown: ue5 }, Re4 = (0, import_react109.useMemo)(() => ({ value: l17 }), [l17]), me4 = (0, import_react109.useCallback)(() => {
    if (b11 !== void 0) return s15(b11);
  }, [s15, b11]), ye6 = L();
  return import_react109.default.createElement(L7, { name: "RadioGroup.Description" }, import_react109.default.createElement(_8, { name: "RadioGroup.Label" }, import_react109.default.createElement(z.Provider, { value: fe4 }, import_react109.default.createElement(J5.Provider, { value: ce5 }, n13 != null && import_react109.default.createElement(j2, { disabled: a25, data: { [n13]: l17 || "on" }, overrides: { type: "radio", checked: l17 != null }, form: D8, onReset: me4 }), ye6({ ourProps: Te4, theirProps: S10, slot: Re4, defaultTag: Me3, name: "RadioGroup" })))));
}
var He3 = "div";
function we2(o21, t12) {
  var g8;
  let e8 = X5("RadioGroup.Option"), i19 = q4("RadioGroup.Option"), v5 = (0, import_react50.useId)(), { id: m11 = `headlessui-radiogroup-option-${v5}`, value: D8, disabled: n13 = e8.disabled || false, autoFocus: f22 = false, ...u19 } = o21, a25 = (0, import_react109.useRef)(null), M11 = y(a25, t12), [T12, S10] = K2(), [R9, A8] = w3(), y8 = s3({ value: D8, disabled: n13 });
  n(() => i19.registerOption({ id: m11, element: a25, propsRef: y8 }), [m11, i19, a25, y8]);
  let p6 = o5((O9) => {
    var s15;
    if (r4(O9.currentTarget)) return O9.preventDefault();
    i19.change(D8) && ((s15 = a25.current) == null || s15.focus());
  }), C10 = ((g8 = e8.firstOption) == null ? void 0 : g8.id) === m11, { isFocusVisible: _8, focusProps: h11 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: f22 }), { isHovered: L7, hoverProps: k5 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: n13 }), c19 = e8.compare(e8.value, D8), b11 = _({ ref: M11, id: m11, role: "radio", "aria-checked": c19 ? "true" : "false", "aria-labelledby": T12, "aria-describedby": R9, "aria-disabled": n13 ? true : void 0, tabIndex: (() => n13 ? -1 : c19 || !e8.containsCheckedOption && C10 ? e8.tabIndex : -1)(), onClick: n13 ? void 0 : p6, autoFocus: f22 }, h11, k5), l17 = (0, import_react109.useMemo)(() => ({ checked: c19, disabled: n13, active: _8, hover: L7, focus: _8, autofocus: f22 }), [c19, n13, L7, _8, f22]), I7 = L();
  return import_react109.default.createElement(A8, { name: "RadioGroup.Description" }, import_react109.default.createElement(S10, { name: "RadioGroup.Label" }, I7({ ourProps: b11, theirProps: u19, slot: l17, defaultTag: He3, name: "RadioGroup.Option" })));
}
var Ne2 = "span";
function We2(o21, t12) {
  var g8;
  let e8 = X5("Radio"), i19 = q4("Radio"), v5 = (0, import_react50.useId)(), m11 = u4(), D8 = a3(), { id: n13 = m11 || `headlessui-radio-${v5}`, value: f22, disabled: u19 = e8.disabled || D8 || false, autoFocus: a25 = false, ...M11 } = o21, T12 = (0, import_react109.useRef)(null), S10 = y(T12, t12), R9 = I(), A8 = U2(), y8 = s3({ value: f22, disabled: u19 });
  n(() => i19.registerOption({ id: n13, element: T12, propsRef: y8 }), [n13, i19, T12, y8]);
  let p6 = o5((O9) => {
    var s15;
    if (r4(O9.currentTarget)) return O9.preventDefault();
    i19.change(f22) && ((s15 = T12.current) == null || s15.focus());
  }), { isFocusVisible: C10, focusProps: _8 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: a25 }), { isHovered: h11, hoverProps: L7 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: u19 }), k5 = ((g8 = e8.firstOption) == null ? void 0 : g8.id) === n13, c19 = e8.compare(e8.value, f22), b11 = _({ ref: S10, id: n13, role: "radio", "aria-checked": c19 ? "true" : "false", "aria-labelledby": R9, "aria-describedby": A8, "aria-disabled": u19 ? true : void 0, tabIndex: (() => u19 ? -1 : c19 || !e8.containsCheckedOption && k5 ? e8.tabIndex : -1)(), autoFocus: a25, onClick: u19 ? void 0 : p6 }, _8, L7), l17 = (0, import_react109.useMemo)(() => ({ checked: c19, disabled: u19, hover: h11, focus: C10, autofocus: a25 }), [c19, u19, h11, C10, a25]);
  return L()({ ourProps: b11, theirProps: M11, slot: l17, defaultTag: Ne2, name: "Radio" });
}
var Be2 = K(Se6);
var Ve = K(we2);
var Ke = K(We2);
var $e2 = Q;
var je3 = H4;
var mt3 = Object.assign(Be2, { Option: Ve, Radio: Ke, Label: $e2, Description: je3 });

// node_modules/@headlessui/react/dist/components/select/select.js
var import_react110 = __toESM(require_react(), 1);
var H12 = "select";
function B4(a25, i19) {
  let p6 = (0, import_react50.useId)(), d14 = u4(), n13 = a3(), { id: c19 = d14 || `headlessui-select-${p6}`, disabled: e8 = n13 || false, invalid: t12 = false, autoFocus: o21 = false, ...f22 } = a25, m11 = I(), u19 = U2(), { isFocusVisible: r20, focusProps: T12 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: o21 }), { isHovered: l17, hoverProps: b11 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), { pressed: s15, pressProps: y8 } = w({ disabled: e8 }), P7 = _({ ref: i19, id: c19, "aria-labelledby": m11, "aria-describedby": u19, "aria-invalid": t12 ? "true" : void 0, disabled: e8 || void 0, autoFocus: o21 }, T12, b11, y8), S10 = (0, import_react110.useMemo)(() => ({ disabled: e8, invalid: t12, hover: l17, focus: r20, active: s15, autofocus: o21 }), [e8, t12, l17, r20, s15, o21]);
  return L()({ ourProps: P7, theirProps: f22, slot: S10, defaultTag: H12, name: "Select" });
}
var j7 = K(B4);

// node_modules/@headlessui/react/dist/components/switch/switch.js
var import_react111 = __toESM(require_react(), 1);
var E13 = (0, import_react111.createContext)(null);
E13.displayName = "GroupContext";
var De4 = import_react111.Fragment;
function ge5(n13) {
  var u19;
  let [o21, s15] = (0, import_react111.useState)(null), [h11, b11] = K2(), [T12, t12] = w3(), p6 = (0, import_react111.useMemo)(() => ({ switch: o21, setSwitch: s15 }), [o21, s15]), y8 = {}, S10 = n13, c19 = L();
  return import_react111.default.createElement(t12, { name: "Switch.Description", value: T12 }, import_react111.default.createElement(b11, { name: "Switch.Label", value: h11, props: { htmlFor: (u19 = p6.switch) == null ? void 0 : u19.id, onClick(d14) {
    o21 && (d14.currentTarget instanceof HTMLLabelElement && d14.preventDefault(), o21.click(), o21.focus({ preventScroll: true }));
  } } }, import_react111.default.createElement(E13.Provider, { value: p6 }, c19({ ourProps: y8, theirProps: S10, slot: {}, defaultTag: De4, name: "Switch.Group" }))));
}
var ve = "button";
function xe2(n13, o21) {
  var L7;
  let s15 = (0, import_react50.useId)(), h11 = u4(), b11 = a3(), { id: T12 = h11 || `headlessui-switch-${s15}`, disabled: t12 = b11 || false, checked: p6, defaultChecked: y8, onChange: S10, name: c19, value: u19, form: d14, autoFocus: m11 = false, ...F9 } = n13, _8 = (0, import_react111.useContext)(E13), [H14, k5] = (0, import_react111.useState)(null), M11 = (0, import_react111.useRef)(null), U7 = y(M11, o21, _8 === null ? null : _8.setSwitch, k5), l17 = l2(y8), [a25, r20] = T(p6, S10, l17 != null ? l17 : false), I7 = p(), [P7, D8] = (0, import_react111.useState)(false), g8 = o5(() => {
    D8(true), r20 == null || r20(!a25), I7.nextFrame(() => {
      D8(false);
    });
  }), B5 = o5((e8) => {
    if (r4(e8.currentTarget)) return e8.preventDefault();
    e8.preventDefault(), g8();
  }), K4 = o5((e8) => {
    e8.key === o9.Space ? (e8.preventDefault(), g8()) : e8.key === o9.Enter && p2(e8.currentTarget);
  }), W3 = o5((e8) => e8.preventDefault()), O9 = I(), N3 = U2(), { isFocusVisible: v5, focusProps: J7 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: m11 }), { isHovered: x11, hoverProps: V6 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: t12 }), { pressed: C10, pressProps: X6 } = w({ disabled: t12 }), j8 = (0, import_react111.useMemo)(() => ({ checked: a25, disabled: t12, hover: x11, focus: v5, active: C10, autofocus: m11, changing: P7 }), [a25, x11, v5, C10, t12, P7, m11]), $4 = _({ id: T12, ref: U7, role: "switch", type: e6(n13, H14), tabIndex: n13.tabIndex === -1 ? 0 : (L7 = n13.tabIndex) != null ? L7 : 0, "aria-checked": a25, "aria-labelledby": O9, "aria-describedby": N3, disabled: t12 || void 0, autoFocus: m11, onClick: B5, onKeyUp: K4, onKeyPress: W3 }, J7, V6, X6), q5 = (0, import_react111.useCallback)(() => {
    if (l17 !== void 0) return r20 == null ? void 0 : r20(l17);
  }, [r20, l17]), z3 = L();
  return import_react111.default.createElement(import_react111.default.Fragment, null, c19 != null && import_react111.default.createElement(j2, { disabled: t12, data: { [c19]: u19 || "on" }, overrides: { type: "checkbox", checked: a25 }, form: d14, onReset: q5 }), z3({ ourProps: $4, theirProps: F9, slot: j8, defaultTag: ve, name: "Switch" }));
}
var Ce2 = K(xe2);
var Le3 = ge5;
var Re3 = Q;
var Ge2 = H4;
var Ye3 = Object.assign(Ce2, { Group: Le3, Label: Re3, Description: Ge2 });

// node_modules/@headlessui/react/dist/components/tabs/tabs.js
var import_react113 = __toESM(require_react(), 1);

// node_modules/@headlessui/react/dist/internal/focus-sentinel.js
var import_react112 = __toESM(require_react(), 1);
function b10({ onFocus: n13 }) {
  let [r20, o21] = (0, import_react112.useState)(true), u19 = f19();
  return r20 ? import_react112.default.createElement(f4, { as: "button", type: "button", features: s4.Focusable, onFocus: (a25) => {
    a25.preventDefault();
    let e8, i19 = 50;
    function t12() {
      if (i19-- <= 0) {
        e8 && cancelAnimationFrame(e8);
        return;
      }
      if (n13()) {
        if (cancelAnimationFrame(e8), !u19.current) return;
        o21(false);
        return;
      }
      e8 = requestAnimationFrame(t12);
    }
    e8 = requestAnimationFrame(t12);
  } }) : null;
}

// node_modules/@headlessui/react/dist/utils/stable-collection.js
var l16 = __toESM(require_react(), 1);
var s14 = l16.createContext(null);
function a24() {
  return { groups: /* @__PURE__ */ new Map(), get(o21, e8) {
    var i19;
    let t12 = this.groups.get(o21);
    t12 || (t12 = /* @__PURE__ */ new Map(), this.groups.set(o21, t12));
    let n13 = (i19 = t12.get(e8)) != null ? i19 : 0;
    t12.set(e8, n13 + 1);
    let r20 = Array.from(t12.keys()).indexOf(e8);
    function u19() {
      let c19 = t12.get(e8);
      c19 > 1 ? t12.set(e8, c19 - 1) : t12.delete(e8);
    }
    return [r20, u19];
  } };
}
function f21({ children: o21 }) {
  let e8 = l16.useRef(a24());
  return l16.createElement(s14.Provider, { value: e8 }, o21);
}
function C8(o21) {
  let e8 = l16.useContext(s14);
  if (!e8) throw new Error("You must wrap your component in a <StableCollection>");
  let t12 = l16.useId(), [n13, r20] = e8.current.get(o21, t12);
  return l16.useEffect(() => r20, []), n13;
}

// node_modules/@headlessui/react/dist/components/tabs/tabs.js
var Le4 = ((t12) => (t12[t12.Forwards = 0] = "Forwards", t12[t12.Backwards = 1] = "Backwards", t12))(Le4 || {});
var _e2 = ((l17) => (l17[l17.Less = -1] = "Less", l17[l17.Equal = 0] = "Equal", l17[l17.Greater = 1] = "Greater", l17))(_e2 || {});
var De5 = ((n13) => (n13[n13.SetSelectedIndex = 0] = "SetSelectedIndex", n13[n13.RegisterTab = 1] = "RegisterTab", n13[n13.UnregisterTab = 2] = "UnregisterTab", n13[n13.RegisterPanel = 3] = "RegisterPanel", n13[n13.UnregisterPanel = 4] = "UnregisterPanel", n13))(De5 || {});
var Se7 = { [0](e8, r20) {
  var d14;
  let t12 = _3(e8.tabs, (u19) => u19.current), l17 = _3(e8.panels, (u19) => u19.current), a25 = t12.filter((u19) => {
    var T12;
    return !((T12 = u19.current) != null && T12.hasAttribute("disabled"));
  }), n13 = { ...e8, tabs: t12, panels: l17 };
  if (r20.index < 0 || r20.index > t12.length - 1) {
    let u19 = u(Math.sign(r20.index - e8.selectedIndex), { [-1]: () => 1, [0]: () => u(Math.sign(r20.index), { [-1]: () => 0, [0]: () => 0, [1]: () => 1 }), [1]: () => 0 });
    if (a25.length === 0) return n13;
    let T12 = u(u19, { [0]: () => t12.indexOf(a25[0]), [1]: () => t12.indexOf(a25[a25.length - 1]) });
    return { ...n13, selectedIndex: T12 === -1 ? e8.selectedIndex : T12 };
  }
  let s15 = t12.slice(0, r20.index), b11 = [...t12.slice(r20.index), ...s15].find((u19) => a25.includes(u19));
  if (!b11) return n13;
  let f22 = (d14 = t12.indexOf(b11)) != null ? d14 : e8.selectedIndex;
  return f22 === -1 && (f22 = e8.selectedIndex), { ...n13, selectedIndex: f22 };
}, [1](e8, r20) {
  if (e8.tabs.includes(r20.tab)) return e8;
  let t12 = e8.tabs[e8.selectedIndex], l17 = _3([...e8.tabs, r20.tab], (n13) => n13.current), a25 = e8.selectedIndex;
  return e8.info.current.isControlled || (a25 = l17.indexOf(t12), a25 === -1 && (a25 = e8.selectedIndex)), { ...e8, tabs: l17, selectedIndex: a25 };
}, [2](e8, r20) {
  return { ...e8, tabs: e8.tabs.filter((t12) => t12 !== r20.tab) };
}, [3](e8, r20) {
  return e8.panels.includes(r20.panel) ? e8 : { ...e8, panels: _3([...e8.panels, r20.panel], (t12) => t12.current) };
}, [4](e8, r20) {
  return { ...e8, panels: e8.panels.filter((t12) => t12 !== r20.panel) };
} };
var V5 = (0, import_react113.createContext)(null);
V5.displayName = "TabsDataContext";
function C9(e8) {
  let r20 = (0, import_react113.useContext)(V5);
  if (r20 === null) {
    let t12 = new Error(`<${e8} /> is missing a parent <Tab.Group /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(t12, C9), t12;
  }
  return r20;
}
var Q3 = (0, import_react113.createContext)(null);
Q3.displayName = "TabsActionsContext";
function Y3(e8) {
  let r20 = (0, import_react113.useContext)(Q3);
  if (r20 === null) {
    let t12 = new Error(`<${e8} /> is missing a parent <Tab.Group /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(t12, Y3), t12;
  }
  return r20;
}
function Fe5(e8, r20) {
  return u(r20.type, Se7, e8, r20);
}
var Ie4 = "div";
function he3(e8, r20) {
  let { defaultIndex: t12 = 0, vertical: l17 = false, manual: a25 = false, onChange: n13, selectedIndex: s15 = null, ...g8 } = e8;
  const b11 = l17 ? "vertical" : "horizontal", f22 = a25 ? "manual" : "auto";
  let d14 = s15 !== null, u19 = s3({ isControlled: d14 }), T12 = y(r20), [p6, c19] = (0, import_react113.useReducer)(Fe5, { info: u19, selectedIndex: s15 != null ? s15 : t12, tabs: [], panels: [] }), h11 = (0, import_react113.useMemo)(() => ({ selectedIndex: p6.selectedIndex }), [p6.selectedIndex]), m11 = s3(n13 || (() => {
  })), M11 = s3(p6.tabs), S10 = (0, import_react113.useMemo)(() => ({ orientation: b11, activation: f22, ...p6 }), [b11, f22, p6]), P7 = o5((i19) => (c19({ type: 1, tab: i19 }), () => c19({ type: 2, tab: i19 }))), A8 = o5((i19) => (c19({ type: 3, panel: i19 }), () => c19({ type: 4, panel: i19 }))), E15 = o5((i19) => {
    _8.current !== i19 && m11.current(i19), d14 || c19({ type: 0, index: i19 });
  }), _8 = s3(d14 ? e8.selectedIndex : p6.selectedIndex), D8 = (0, import_react113.useMemo)(() => ({ registerTab: P7, registerPanel: A8, change: E15 }), []);
  n(() => {
    c19({ type: 0, index: s15 != null ? s15 : t12 });
  }, [s15]), n(() => {
    if (_8.current === void 0 || p6.tabs.length <= 0) return;
    let i19 = _3(p6.tabs, (R9) => R9.current);
    i19.some((R9, X6) => p6.tabs[X6] !== R9) && E15(i19.indexOf(p6.tabs[_8.current]));
  });
  let K4 = { ref: T12 }, J7 = L();
  return import_react113.default.createElement(f21, null, import_react113.default.createElement(Q3.Provider, { value: D8 }, import_react113.default.createElement(V5.Provider, { value: S10 }, S10.tabs.length <= 0 && import_react113.default.createElement(b10, { onFocus: () => {
    var i19, G8;
    for (let R9 of M11.current) if (((i19 = R9.current) == null ? void 0 : i19.tabIndex) === 0) return (G8 = R9.current) == null || G8.focus(), true;
    return false;
  } }), J7({ ourProps: K4, theirProps: g8, slot: h11, defaultTag: Ie4, name: "Tabs" }))));
}
var ve2 = "div";
function Ce3(e8, r20) {
  let { orientation: t12, selectedIndex: l17 } = C9("Tab.List"), a25 = y(r20), n13 = (0, import_react113.useMemo)(() => ({ selectedIndex: l17 }), [l17]), s15 = e8, g8 = { ref: a25, role: "tablist", "aria-orientation": t12 };
  return L()({ ourProps: g8, theirProps: s15, slot: n13, defaultTag: ve2, name: "Tabs.List" });
}
var Me4 = "button";
function Ge3(e8, r20) {
  var ee4, te6;
  let t12 = (0, import_react50.useId)(), { id: l17 = `headlessui-tabs-tab-${t12}`, disabled: a25 = false, autoFocus: n13 = false, ...s15 } = e8, { orientation: g8, activation: b11, selectedIndex: f22, tabs: d14, panels: u19 } = C9("Tab"), T12 = Y3("Tab"), p6 = C9("Tab"), [c19, h11] = (0, import_react113.useState)(null), m11 = (0, import_react113.useRef)(null), M11 = y(m11, r20, h11);
  n(() => T12.registerTab(m11), [T12, m11]);
  let S10 = C8("tabs"), P7 = d14.indexOf(m11);
  P7 === -1 && (P7 = S10);
  let A8 = P7 === f22, E15 = o5((o21) => {
    var $4;
    let L7 = o21();
    if (L7 === T5.Success && b11 === "auto") {
      let q5 = ($4 = o2(m11)) == null ? void 0 : $4.activeElement, re6 = p6.tabs.findIndex((ce5) => ce5.current === q5);
      re6 !== -1 && T12.change(re6);
    }
    return L7;
  }), _8 = o5((o21) => {
    let L7 = d14.map((q5) => q5.current).filter(Boolean);
    if (o21.key === o9.Space || o21.key === o9.Enter) {
      o21.preventDefault(), o21.stopPropagation(), T12.change(P7);
      return;
    }
    switch (o21.key) {
      case o9.Home:
      case o9.PageUp:
        return o21.preventDefault(), o21.stopPropagation(), E15(() => P6(L7, F2.First));
      case o9.End:
      case o9.PageDown:
        return o21.preventDefault(), o21.stopPropagation(), E15(() => P6(L7, F2.Last));
    }
    if (E15(() => u(g8, { vertical() {
      return o21.key === o9.ArrowUp ? P6(L7, F2.Previous | F2.WrapAround) : o21.key === o9.ArrowDown ? P6(L7, F2.Next | F2.WrapAround) : T5.Error;
    }, horizontal() {
      return o21.key === o9.ArrowLeft ? P6(L7, F2.Previous | F2.WrapAround) : o21.key === o9.ArrowRight ? P6(L7, F2.Next | F2.WrapAround) : T5.Error;
    } })) === T5.Success) return o21.preventDefault();
  }), D8 = (0, import_react113.useRef)(false), K4 = o5(() => {
    var o21;
    D8.current || (D8.current = true, (o21 = m11.current) == null || o21.focus({ preventScroll: true }), T12.change(P7), t(() => {
      D8.current = false;
    }));
  }), J7 = o5((o21) => {
    o21.preventDefault();
  }), { isFocusVisible: i19, focusProps: G8 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: n13 }), { isHovered: R9, hoverProps: X6 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: a25 }), { pressed: Z4, pressProps: ue5 } = w({ disabled: a25 }), Te4 = (0, import_react113.useMemo)(() => ({ selected: A8, hover: R9, active: Z4, focus: i19, autofocus: n13, disabled: a25 }), [A8, R9, i19, Z4, n13, a25]), de8 = _({ ref: M11, onKeyDown: _8, onMouseDown: J7, onClick: K4, id: l17, role: "tab", type: e6(e8, c19), "aria-controls": (te6 = (ee4 = u19[P7]) == null ? void 0 : ee4.current) == null ? void 0 : te6.id, "aria-selected": A8, tabIndex: A8 ? 0 : -1, disabled: a25 || void 0, autoFocus: n13 }, G8, X6, ue5);
  return L()({ ourProps: de8, theirProps: s15, slot: Te4, defaultTag: Me4, name: "Tabs.Tab" });
}
var Ue3 = "div";
function He4(e8, r20) {
  let { selectedIndex: t12 } = C9("Tab.Panels"), l17 = y(r20), a25 = (0, import_react113.useMemo)(() => ({ selectedIndex: t12 }), [t12]), n13 = e8, s15 = { ref: l17 };
  return L()({ ourProps: s15, theirProps: n13, slot: a25, defaultTag: Ue3, name: "Tabs.Panels" });
}
var we3 = "div";
var Oe4 = O.RenderStrategy | O.Static;
function Ne3(e8, r20) {
  var A8, E15, _8, D8;
  let t12 = (0, import_react50.useId)(), { id: l17 = `headlessui-tabs-panel-${t12}`, tabIndex: a25 = 0, ...n13 } = e8, { selectedIndex: s15, tabs: g8, panels: b11 } = C9("Tab.Panel"), f22 = Y3("Tab.Panel"), d14 = (0, import_react113.useRef)(null), u19 = y(d14, r20);
  n(() => f22.registerPanel(d14), [f22, d14]);
  let T12 = C8("panels"), p6 = b11.indexOf(d14);
  p6 === -1 && (p6 = T12);
  let c19 = p6 === s15, { isFocusVisible: h11, focusProps: m11 } = $f7dceffc5ad7768b$export$4e328f61c538687f(), M11 = (0, import_react113.useMemo)(() => ({ selected: c19, focus: h11 }), [c19, h11]), S10 = _({ ref: u19, id: l17, role: "tabpanel", "aria-labelledby": (E15 = (A8 = g8[p6]) == null ? void 0 : A8.current) == null ? void 0 : E15.id, tabIndex: c19 ? a25 : -1 }, m11), P7 = L();
  return !c19 && ((_8 = n13.unmount) == null || _8) && !((D8 = n13.static) != null && D8) ? import_react113.default.createElement(f4, { "aria-hidden": "true", ...S10 }) : P7({ ourProps: S10, theirProps: n13, slot: M11, defaultTag: we3, features: Oe4, visible: c19, name: "Tabs.Panel" });
}
var ke2 = K(Ge3);
var Be3 = K(he3);
var We3 = K(Ce3);
var je4 = K(He4);
var Ke2 = K(Ne3);
var Tt3 = Object.assign(ke2, { Group: Be3, List: We3, Panels: je4, Panel: Ke2 });

// node_modules/@headlessui/react/dist/components/textarea/textarea.js
var import_react114 = __toESM(require_react(), 1);
var L6 = "textarea";
function H13(s15, l17) {
  let i19 = (0, import_react50.useId)(), d14 = u4(), n13 = a3(), { id: p6 = d14 || `headlessui-textarea-${i19}`, disabled: e8 = n13 || false, autoFocus: r20 = false, invalid: a25 = false, ...T12 } = s15, f22 = I(), m11 = U2(), { isFocused: o21, focusProps: u19 } = $f7dceffc5ad7768b$export$4e328f61c538687f({ autoFocus: r20 }), { isHovered: t12, hoverProps: b11 } = $6179b936705e76d3$export$ae780daf29e6d456({ isDisabled: e8 }), y8 = _({ ref: l17, id: p6, "aria-labelledby": f22, "aria-describedby": m11, "aria-invalid": a25 ? "true" : void 0, disabled: e8 || void 0, autoFocus: r20 }, u19, b11), x11 = (0, import_react114.useMemo)(() => ({ disabled: e8, invalid: a25, hover: t12, focus: o21, autofocus: r20 }), [e8, a25, t12, o21, r20]);
  return L()({ ourProps: y8, theirProps: T12, slot: x11, defaultTag: L6, name: "Textarea" });
}
var J6 = K(H13);
export {
  H2 as Button,
  Fe as Checkbox,
  y2 as CloseButton,
  Dt as Combobox,
  Do as ComboboxButton,
  Fo as ComboboxInput,
  So as ComboboxLabel,
  Lo as ComboboxOption,
  Mo as ComboboxOptions,
  x5 as DataInteractive,
  H4 as Description,
  yt as Dialog,
  Dt2 as DialogBackdrop,
  Pt as DialogDescription,
  je as DialogPanel,
  Ye as DialogTitle,
  je2 as Disclosure,
  Ce as DisclosureButton,
  Re2 as DisclosurePanel,
  H10 as Field,
  G5 as Fieldset,
  ye2 as FocusTrap,
  x6 as FocusTrapFeatures,
  S8 as Input,
  Q as Label,
  d13 as Legend,
  Ao2 as Listbox,
  Rt as ListboxButton,
  Ft as ListboxLabel,
  Mt as ListboxOption,
  Ct as ListboxOptions,
  wt as ListboxSelectedOption,
  to as Menu,
  mt as MenuButton,
  yt2 as MenuHeading,
  ft as MenuItem,
  Tt as MenuItems,
  ct as MenuSection,
  Et2 as MenuSeparator,
  ao as Popover,
  Rt2 as PopoverBackdrop,
  At2 as PopoverButton,
  _t2 as PopoverGroup,
  Ct2 as PopoverOverlay,
  Bt as PopoverPanel,
  oe as Portal,
  Ke as Radio,
  mt3 as RadioGroup,
  je3 as RadioGroupDescription,
  $e2 as RadioGroupLabel,
  Ve as RadioGroupOption,
  j7 as Select,
  Ye3 as Switch,
  Ge2 as SwitchDescription,
  Le3 as SwitchGroup,
  Re3 as SwitchLabel,
  Tt3 as Tab,
  Be3 as TabGroup,
  We3 as TabList,
  Ke2 as TabPanel,
  je4 as TabPanels,
  J6 as Textarea,
  ze as Transition,
  Fe3 as TransitionChild,
  u7 as useClose
};
/*! Bundled license information:

use-sync-external-store/cjs/use-sync-external-store-with-selector.development.js:
  (**
   * @license React
   * use-sync-external-store-with-selector.development.js
   *
   * Copyright (c) Meta Platforms, Inc. and affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   *)

tabbable/dist/index.esm.js:
  (*!
  * tabbable 6.2.0
  * @license MIT, https://github.com/focus-trap/tabbable/blob/master/LICENSE
  *)
*/
//# sourceMappingURL=@headlessui_react.js.map
