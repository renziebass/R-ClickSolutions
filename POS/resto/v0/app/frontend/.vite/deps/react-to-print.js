import {
  __commonJS,
  require_react
} from "./chunk-VCDLJVZS.js";

// node_modules/react-to-print/lib/index.js
var require_lib = __commonJS({
  "node_modules/react-to-print/lib/index.js"(exports, module) {
    !function(e, t) {
      "object" == typeof exports && "object" == typeof module ? module.exports = t(require_react()) : "function" == typeof define && define.amd ? define("lib", ["react"], t) : "object" == typeof exports ? exports.lib = t(require_react()) : e.lib = t(e.react);
    }("undefined" != typeof self ? self : exports, function(e) {
      return function() {
        "use strict";
        var t = { 155: function(t2) {
          t2.exports = e;
        } }, o = {};
        function n(e2) {
          var r2 = o[e2];
          if (void 0 !== r2) return r2.exports;
          var s2 = o[e2] = { exports: {} };
          return t[e2](s2, s2.exports, n), s2.exports;
        }
        n.d = function(e2, t2) {
          for (var o2 in t2) n.o(t2, o2) && !n.o(e2, o2) && Object.defineProperty(e2, o2, { enumerable: true, get: t2[o2] });
        }, n.o = function(e2, t2) {
          return Object.prototype.hasOwnProperty.call(e2, t2);
        }, n.r = function(e2) {
          "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e2, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(e2, "__esModule", { value: true });
        };
        var r = {};
        n.r(r), n.d(r, { useReactToPrint: function() {
          return f;
        } });
        var s = n(155);
        function i({ level: e2 = "error", messages: t2, suppressErrors: o2 = false }) {
          o2 || ("error" === e2 ? console.error(t2) : "warning" === e2 ? console.warn(t2) : console.debug(t2));
        }
        function l(e2, t2) {
          if (t2 || !e2) {
            const e3 = document.getElementById("printWindow");
            e3 && document.body.removeChild(e3);
          }
        }
        function a(e2) {
          return e2 instanceof Error ? e2 : new Error("Unknown Error");
        }
        function c(e2, t2) {
          const { documentTitle: o2, onAfterPrint: n2, onPrintError: r2, preserveAfterPrint: s2, print: c2, suppressErrors: d2 } = t2;
          setTimeout(() => {
            var t3, u2;
            if (e2.contentWindow) {
              let p2 = function() {
                null == n2 || n2(), l(s2);
              };
              if (e2.contentWindow.focus(), c2) c2(e2).then(p2).catch((e3) => {
                r2 ? r2("print", a(e3)) : i({ messages: ["An error was thrown by the specified `print` function"], suppressErrors: d2 });
              });
              else {
                if (e2.contentWindow.print) {
                  const h2 = null !== (u2 = null === (t3 = e2.contentDocument) || void 0 === t3 ? void 0 : t3.title) && void 0 !== u2 ? u2 : "", f2 = e2.ownerDocument.title;
                  o2 && (e2.ownerDocument.title = o2, e2.contentDocument && (e2.contentDocument.title = o2)), e2.contentWindow.print(), o2 && (e2.ownerDocument.title = f2, e2.contentDocument && (e2.contentDocument.title = h2));
                } else i({ messages: ["Printing for this browser is not currently possible: the browser does not have a `print` method available for iframes."], suppressErrors: d2 });
                [/Android/i, /webOS/i, /iPhone/i, /iPad/i, /iPod/i, /BlackBerry/i, /Windows Phone/i].some((e3) => {
                  var t4, o3;
                  return (null !== (o3 = null !== (t4 = navigator.userAgent) && void 0 !== t4 ? t4 : navigator.vendor) && void 0 !== o3 ? o3 : "opera" in window && window.opera).match(e3);
                }) ? setTimeout(p2, 500) : p2();
              }
            } else i({ messages: ["Printing failed because the `contentWindow` of the print iframe did not load. This is possibly an error with `react-to-print`. Please file an issue: https://github.com/MatthewHerbst/react-to-print/issues/"], suppressErrors: d2 });
          }, 500);
        }
        function d(e2) {
          const t2 = [], o2 = document.createTreeWalker(e2, NodeFilter.SHOW_ELEMENT, null);
          let n2 = o2.nextNode();
          for (; n2; ) t2.push(n2), n2 = o2.nextNode();
          return t2;
        }
        function u(e2, t2, o2) {
          const n2 = d(e2), r2 = d(t2);
          if (n2.length === r2.length) for (let e3 = 0; e3 < n2.length; e3++) {
            const t3 = n2[e3], s2 = r2[e3], i2 = t3.shadowRoot;
            if (null !== i2) {
              const e4 = s2.attachShadow({ mode: i2.mode });
              e4.innerHTML = i2.innerHTML, u(i2, e4, o2);
            }
          }
          else i({ messages: ["When cloning shadow root content, source and target elements have different size. `onBeforePrint` likely resolved too early.", e2, t2], suppressErrors: o2 });
        }
        const p = '\n    @page {\n        /* Remove browser default header (title) and footer (url) */\n        margin: 0;\n    }\n    @media print {\n        body {\n            /* Tell browsers to print background colors */\n            color-adjust: exact; /* Firefox. This is an older version of "print-color-adjust" */\n            print-color-adjust: exact; /* Firefox/Safari */\n            -webkit-print-color-adjust: exact; /* Chrome/Safari/Edge/Opera */\n        }\n    }\n';
        function h(e2, t2, o2, n2) {
          var r2, s2, l2, d2, h2;
          const { contentNode: f2, clonedContentNode: g, clonedImgNodes: m, clonedVideoNodes: b, numResourcesToLoad: y, originalCanvasNodes: v } = o2, { bodyClass: w, fonts: E, ignoreGlobalStyles: A, pageStyle: T, nonce: S, suppressErrors: P, copyShadowRoots: k } = n2;
          e2.onload = null;
          const x = null !== (r2 = e2.contentDocument) && void 0 !== r2 ? r2 : null === (s2 = e2.contentWindow) || void 0 === s2 ? void 0 : s2.document;
          if (x) {
            const o3 = x.body.appendChild(g);
            k && u(f2, o3, !!P), E && ((null === (l2 = e2.contentDocument) || void 0 === l2 ? void 0 : l2.fonts) && (null === (d2 = e2.contentWindow) || void 0 === d2 ? void 0 : d2.FontFace) ? E.forEach((o4) => {
              const n4 = new FontFace(o4.family, o4.source, { weight: o4.weight, style: o4.style });
              e2.contentDocument.fonts.add(n4), n4.loaded.then(() => {
                t2(n4);
              }).catch((e3) => {
                t2(n4, ["Failed loading the font:", n4, "Load error:", a(e3)]);
              });
            }) : (E.forEach((e3) => {
              t2(e3);
            }), i({ messages: ['"react-to-print" is not able to load custom fonts because the browser does not support the FontFace API but will continue attempting to print the page'], suppressErrors: P })));
            const n3 = null != T ? T : p, r3 = x.createElement("style");
            S && (r3.setAttribute("nonce", S), x.head.setAttribute("nonce", S)), r3.appendChild(x.createTextNode(n3)), x.head.appendChild(r3), w && x.body.classList.add(...w.split(" "));
            const s3 = x.querySelectorAll("canvas");
            for (let e3 = 0; e3 < v.length; ++e3) {
              const t3 = v[e3], o4 = s3[e3];
              if (void 0 === o4) {
                i({ messages: ["A canvas element could not be copied for printing, has it loaded? `onBeforePrint` likely resolved too early.", t3], suppressErrors: P });
                continue;
              }
              const n4 = o4.getContext("2d");
              n4 && n4.drawImage(t3, 0, 0);
            }
            for (let e3 = 0; e3 < m.length; e3++) {
              const o4 = m[e3], n4 = o4.getAttribute("src");
              if (n4) {
                const e4 = new Image();
                e4.onload = () => {
                  t2(o4);
                }, e4.onerror = (e5, n5, r4, s4, i2) => {
                  t2(o4, ["Error loading <img>", o4, "Error", i2]);
                }, e4.src = n4;
              } else t2(o4, ['Found an <img> tag with an empty "src" attribute. This prevents pre-loading it.', o4]);
            }
            for (let e3 = 0; e3 < b.length; e3++) {
              const o4 = b[e3];
              o4.preload = "auto";
              const n4 = o4.getAttribute("poster");
              if (n4) {
                const e4 = new Image();
                e4.onload = () => {
                  t2(o4);
                }, e4.onerror = (e5, r4, s4, i2, l3) => {
                  t2(o4, ["Error loading video poster", n4, "for video", o4, "Error:", l3]);
                }, e4.src = n4;
              } else o4.readyState >= 2 ? t2(o4) : o4.src ? (o4.onloadeddata = () => {
                t2(o4);
              }, o4.onerror = (e4, n5, r4, s4, i2) => {
                t2(o4, ["Error loading video", o4, "Error", i2]);
              }, o4.onstalled = () => {
                t2(o4, ["Loading video stalled, skipping", o4]);
              }) : t2(o4, ["Error loading video, `src` is empty", o4]);
            }
            const c2 = "select", y2 = f2.querySelectorAll(c2), C = x.querySelectorAll(c2);
            for (let e3 = 0; e3 < y2.length; e3++) C[e3].value = y2[e3].value;
            if (!A) {
              const e3 = document.querySelectorAll("style, link[rel~='stylesheet'], link[as='style']");
              for (let o4 = 0, n4 = e3.length; o4 < n4; ++o4) {
                const n5 = e3[o4];
                if ("style" === n5.tagName.toLowerCase()) {
                  const e4 = x.createElement(n5.tagName), t3 = n5.sheet;
                  if (t3) {
                    let r4 = "";
                    try {
                      const e5 = t3.cssRules.length;
                      for (let o5 = 0; o5 < e5; ++o5) "string" == typeof t3.cssRules[o5].cssText && (r4 += `${t3.cssRules[o5].cssText}\r
`);
                    } catch (e5) {
                      i({ messages: ["A stylesheet could not be accessed. This is likely due to the stylesheet having cross-origin imports, and many browsers block script access to cross-origin stylesheets. See https://github.com/MatthewHerbst/react-to-print/issues/429 for details. You may be able to load the sheet by both marking the stylesheet with the cross `crossorigin` attribute, and setting the `Access-Control-Allow-Origin` header on the server serving the stylesheet. Alternatively, host the stylesheet on your domain to avoid this issue entirely.", n5, `Original error: ${a(e5).message}`], level: "warning" });
                    }
                    e4.setAttribute("id", `react-to-print-${o4}`), S && e4.setAttribute("nonce", S), e4.appendChild(x.createTextNode(r4)), x.head.appendChild(e4);
                  }
                } else if (n5.getAttribute("href")) if (n5.hasAttribute("disabled")) i({ messages: ["`react-to-print` encountered a <link> tag with a `disabled` attribute and will ignore it. Note that the `disabled` attribute is deprecated, and some browsers ignore it. You should stop using it. https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link#attr-disabled. The <link> is:", n5], level: "warning" }), t2(n5);
                else {
                  const e4 = x.createElement(n5.tagName);
                  for (let t3 = 0, o5 = n5.attributes.length; t3 < o5; ++t3) {
                    const o6 = n5.attributes[t3];
                    o6 && e4.setAttribute(o6.nodeName, null !== (h2 = o6.nodeValue) && void 0 !== h2 ? h2 : "");
                  }
                  e4.onload = () => {
                    t2(e4);
                  }, e4.onerror = (o5, n6, r4, s4, i2) => {
                    t2(e4, ["Failed to load", e4, "Error:", i2]);
                  }, S && e4.setAttribute("nonce", S), x.head.appendChild(e4);
                }
                else i({ messages: ["`react-to-print` encountered a <link> tag with an empty `href` attribute. In addition to being invalid HTML, this can cause problems in many browsers, and so the <link> was not loaded. The <link> is:", n5], level: "warning" }), t2(n5);
              }
            }
          }
          0 === y && c(e2, n2);
        }
        function f({ bodyClass: e2, contentRef: t2, copyShadowRoots: o2, documentTitle: n2, fonts: r2, ignoreGlobalStyles: d2, nonce: u2, onAfterPrint: p2, onBeforePrint: f2, onPrintError: g, pageStyle: m, preserveAfterPrint: b, print: y, suppressErrors: v }) {
          return (0, s.useCallback)((s2) => {
            function w() {
              const l2 = { bodyClass: e2, contentRef: t2, copyShadowRoots: o2, documentTitle: n2, fonts: r2, ignoreGlobalStyles: d2, nonce: u2, onAfterPrint: p2, onBeforePrint: f2, onPrintError: g, pageStyle: m, preserveAfterPrint: b, print: y, suppressErrors: v }, a2 = function() {
                const e3 = document.createElement("iframe");
                return e3.width = `${document.documentElement.clientWidth}px`, e3.height = `${document.documentElement.clientHeight}px`, e3.style.position = "absolute", e3.style.top = `-${document.documentElement.clientHeight + 100}px`, e3.style.left = `-${document.documentElement.clientWidth + 100}px`, e3.id = "printWindow", e3.srcdoc = "<!DOCTYPE html>", e3;
              }(), w2 = function(e3, t3) {
                const { contentRef: o3, fonts: n3, ignoreGlobalStyles: r3, suppressErrors: s3 } = t3, l3 = function({ contentRef: e4, optionalContent: t4, suppressErrors: o4 }) {
                  return !t4 || t4 instanceof Event || (e4 && i({ level: "warning", messages: ['"react-to-print" received a `contentRef` option and a optional-content param passed to its callback. The `contentRef` option will be ignored.'] }), "function" != typeof t4) ? e4 ? e4.current : void i({ messages: ['"react-to-print" did not receive a `contentRef` option or a optional-content param pass to its callback.'], suppressErrors: o4 }) : t4();
                }({ contentRef: o3, optionalContent: e3, suppressErrors: s3 });
                if (!l3) return;
                const a3 = l3.cloneNode(true), c2 = document.querySelectorAll("link[rel~='stylesheet'], link[as='style']"), d3 = a3.querySelectorAll("img"), u3 = a3.querySelectorAll("video"), p3 = n3 ? n3.length : 0;
                return { contentNode: l3, clonedContentNode: a3, clonedImgNodes: d3, clonedVideoNodes: u3, numResourcesToLoad: (r3 ? 0 : c2.length) + d3.length + u3.length + p3, originalCanvasNodes: l3.querySelectorAll("canvas") };
              }(s2, l2);
              if (!w2) return void i({ messages: ["There is nothing to print"], suppressErrors: v });
              const E = function(e3, t3, o3) {
                const { suppressErrors: n3 } = e3, r3 = [], s3 = [];
                return function(l3, a3) {
                  r3.includes(l3) ? i({ level: "debug", messages: ["Tried to mark a resource that has already been handled", l3], suppressErrors: n3 }) : (a3 ? (i({ messages: ['"react-to-print" was unable to load a resource but will continue attempting to print the page', ...a3], suppressErrors: n3 }), s3.push(l3)) : r3.push(l3), r3.length + s3.length === t3 && c(o3, e3));
                };
              }(l2, w2.numResourcesToLoad, a2);
              !function(e3, t3, o3, n3) {
                e3.onload = () => {
                  h(e3, t3, o3, n3);
                }, document.body.appendChild(e3);
              }(a2, E, w2, l2);
            }
            l(b, true), f2 ? f2().then(() => {
              w();
            }).catch((e3) => {
              null == g || g("onBeforePrint", a(e3));
            }) : w();
          }, [e2, t2, o2, n2, r2, d2, u2, p2, f2, g, m, b, y, v]);
        }
        return r;
      }();
    });
  }
});
export default require_lib();
//# sourceMappingURL=react-to-print.js.map
