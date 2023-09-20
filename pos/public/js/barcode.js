/*! JsBarcode v3.11.5 | (c) Johan Lindell | MIT license */
!(function (t) {
    var e = {};
    function n(r) {
        if (e[r]) return e[r].exports;
        var o = (e[r] = { i: r, l: !1, exports: {} });
        return t[r].call(o.exports, o, o.exports, n), (o.l = !0), o.exports;
    }
    (n.m = t),
        (n.c = e),
        (n.d = function (t, e, r) {
            n.o(t, e) ||
                Object.defineProperty(t, e, { enumerable: !0, get: r });
        }),
        (n.r = function (t) {
            "undefined" != typeof Symbol &&
                Symbol.toStringTag &&
                Object.defineProperty(t, Symbol.toStringTag, {
                    value: "Module",
                }),
                Object.defineProperty(t, "__esModule", { value: !0 });
        }),
        (n.t = function (t, e) {
            if ((1 & e && (t = n(t)), 8 & e)) return t;
            if (4 & e && "object" == typeof t && t && t.__esModule) return t;
            var r = Object.create(null);
            if (
                (n.r(r),
                Object.defineProperty(r, "default", {
                    enumerable: !0,
                    value: t,
                }),
                2 & e && "string" != typeof t)
            )
                for (var o in t)
                    n.d(
                        r,
                        o,
                        function (e) {
                            return t[e];
                        }.bind(null, o)
                    );
            return r;
        }),
        (n.n = function (t) {
            var e =
                t && t.__esModule
                    ? function () {
                          return t.default;
                      }
                    : function () {
                          return t;
                      };
            return n.d(e, "a", e), e;
        }),
        (n.o = function (t, e) {
            return Object.prototype.hasOwnProperty.call(t, e);
        }),
        (n.p = ""),
        n((n.s = 15));
})([
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        e.default = function t(e, n) {
            !(function (t, e) {
                if (!(t instanceof e))
                    throw new TypeError("Cannot call a class as a function");
            })(this, t),
                (this.data = e),
                (this.text = n.text || e),
                (this.options = n);
        };
    },
    function (t, e, n) {
        "use strict";
        var r;
        function o(t, e, n) {
            return (
                e in t
                    ? Object.defineProperty(t, e, {
                          value: n,
                          enumerable: !0,
                          configurable: !0,
                          writable: !0,
                      })
                    : (t[e] = n),
                t
            );
        }
        Object.defineProperty(e, "__esModule", { value: !0 });
        var i = (e.SET_A = 0),
            a = (e.SET_B = 1),
            u = (e.SET_C = 2),
            f = ((e.SHIFT = 98), (e.START_A = 103)),
            c = (e.START_B = 104),
            s = (e.START_C = 105);
        (e.MODULO = 103),
            (e.STOP = 106),
            (e.FNC1 = 207),
            (e.SET_BY_CODE = (o((r = {}), f, i), o(r, c, a), o(r, s, u), r)),
            (e.SWAP = { 101: i, 100: a, 99: u }),
            (e.A_START_CHAR = String.fromCharCode(208)),
            (e.B_START_CHAR = String.fromCharCode(209)),
            (e.C_START_CHAR = String.fromCharCode(210)),
            (e.A_CHARS = "[\0-_È-Ï]"),
            (e.B_CHARS = "[ -È-Ï]"),
            (e.C_CHARS = "(Ï*[0-9]{2}Ï*)"),
            (e.BARS = [
                11011001100, 11001101100, 11001100110, 10010011e3, 10010001100,
                10001001100, 10011001e3, 10011000100, 10001100100, 11001001e3,
                11001000100, 11000100100, 10110011100, 10011011100, 10011001110,
                10111001100, 10011101100, 10011100110, 11001110010, 11001011100,
                11001001110, 11011100100, 11001110100, 11101101110, 11101001100,
                11100101100, 11100100110, 11101100100, 11100110100, 11100110010,
                11011011e3, 11011000110, 11000110110, 10100011e3, 10001011e3,
                10001000110, 10110001e3, 10001101e3, 10001100010, 11010001e3,
                11000101e3, 11000100010, 10110111e3, 10110001110, 10001101110,
                10111011e3, 10111000110, 10001110110, 11101110110, 11010001110,
                11000101110, 11011101e3, 11011100010, 11011101110, 11101011e3,
                11101000110, 11100010110, 11101101e3, 11101100010, 11100011010,
                11101111010, 11001000010, 11110001010, 1010011e4, 10100001100,
                1001011e4, 10010000110, 10000101100, 10000100110, 1011001e4,
                10110000100, 1001101e4, 10011000010, 10000110100, 10000110010,
                11000010010, 1100101e4, 11110111010, 11000010100, 10001111010,
                10100111100, 10010111100, 10010011110, 10111100100, 10011110100,
                10011110010, 11110100100, 11110010100, 11110010010, 11011011110,
                11011110110, 11110110110, 10101111e3, 10100011110, 10001011110,
                10111101e3, 10111100010, 11110101e3, 11110100010, 10111011110,
                10111101110, 11101011110, 11110101110, 11010000100, 1101001e4,
                11010011100, 1100011101011,
            ]);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        (e.SIDE_BIN = "101"),
            (e.MIDDLE_BIN = "01010"),
            (e.BINARIES = {
                L: [
                    "0001101",
                    "0011001",
                    "0010011",
                    "0111101",
                    "0100011",
                    "0110001",
                    "0101111",
                    "0111011",
                    "0110111",
                    "0001011",
                ],
                G: [
                    "0100111",
                    "0110011",
                    "0011011",
                    "0100001",
                    "0011101",
                    "0111001",
                    "0000101",
                    "0010001",
                    "0001001",
                    "0010111",
                ],
                R: [
                    "1110010",
                    "1100110",
                    "1101100",
                    "1000010",
                    "1011100",
                    "1001110",
                    "1010000",
                    "1000100",
                    "1001000",
                    "1110100",
                ],
                O: [
                    "0001101",
                    "0011001",
                    "0010011",
                    "0111101",
                    "0100011",
                    "0110001",
                    "0101111",
                    "0111011",
                    "0110111",
                    "0001011",
                ],
                E: [
                    "0100111",
                    "0110011",
                    "0011011",
                    "0100001",
                    "0011101",
                    "0111001",
                    "0000101",
                    "0010001",
                    "0001001",
                    "0010111",
                ],
            }),
            (e.EAN2_STRUCTURE = ["LL", "LG", "GL", "GG"]),
            (e.EAN5_STRUCTURE = [
                "GGLLL",
                "GLGLL",
                "GLLGL",
                "GLLLG",
                "LGGLL",
                "LLGGL",
                "LLLGG",
                "LGLGL",
                "LGLLG",
                "LLGLG",
            ]),
            (e.EAN13_STRUCTURE = [
                "LLLLLL",
                "LLGLGG",
                "LLGGLG",
                "LLGGGL",
                "LGLLGG",
                "LGGLLG",
                "LGGGLL",
                "LGLGLG",
                "LGLGGL",
                "LGGLGL",
            ]);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = n(2);
        e.default = function (t, e, n) {
            var o = t
                .split("")
                .map(function (t, n) {
                    return r.BINARIES[e[n]];
                })
                .map(function (e, n) {
                    return e ? e[t[n]] : "";
                });
            if (n) {
                var i = t.length - 1;
                o = o.map(function (t, e) {
                    return e < i ? t + n : t;
                });
            }
            return o.join("");
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0);
        var a = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "encode",
                        value: function () {
                            for (
                                var t = "110", e = 0;
                                e < this.data.length;
                                e++
                            ) {
                                var n = parseInt(this.data[e]).toString(2);
                                n = u(n, 4 - n.length);
                                for (var r = 0; r < n.length; r++)
                                    t += "0" == n[r] ? "100" : "110";
                            }
                            return { data: (t += "1001"), text: this.text };
                        },
                    },
                    {
                        key: "valid",
                        value: function () {
                            return -1 !== this.data.search(/^[0-9]+$/);
                        },
                    },
                ]),
                e
            );
        })(((r = i) && r.__esModule ? r : { default: r }).default);
        function u(t, e) {
            for (var n = 0; n < e; n++) t = "0" + t;
            return t;
        }
        e.default = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(1);
        var f = (function (t) {
            function e(t, n) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, e);
                var r = (function (t, e) {
                    if (!t)
                        throw new ReferenceError(
                            "this hasn't been initialised - super() hasn't been called"
                        );
                    return !e ||
                        ("object" != typeof e && "function" != typeof e)
                        ? t
                        : e;
                })(
                    this,
                    (e.__proto__ || Object.getPrototypeOf(e)).call(
                        this,
                        t.substring(1),
                        n
                    )
                );
                return (
                    (r.bytes = t.split("").map(function (t) {
                        return t.charCodeAt(0);
                    })),
                    r
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(
                    e,
                    [
                        {
                            key: "valid",
                            value: function () {
                                return /^[\x00-\x7F\xC8-\xD3]+$/.test(
                                    this.data
                                );
                            },
                        },
                        {
                            key: "encode",
                            value: function () {
                                var t = this.bytes,
                                    n = t.shift() - 105,
                                    r = u.SET_BY_CODE[n];
                                if (void 0 === r)
                                    throw new RangeError(
                                        "The encoding does not start with a start character."
                                    );
                                !0 === this.shouldEncodeAsEan128() &&
                                    t.unshift(u.FNC1);
                                var o = e.next(t, 1, r);
                                return {
                                    text:
                                        this.text === this.data
                                            ? this.text.replace(
                                                  /[^\x20-\x7E]/g,
                                                  ""
                                              )
                                            : this.text,
                                    data:
                                        e.getBar(n) +
                                        o.result +
                                        e.getBar((o.checksum + n) % u.MODULO) +
                                        e.getBar(u.STOP),
                                };
                            },
                        },
                        {
                            key: "shouldEncodeAsEan128",
                            value: function () {
                                var t = this.options.ean128 || !1;
                                return (
                                    "string" == typeof t &&
                                        (t = "true" === t.toLowerCase()),
                                    t
                                );
                            },
                        },
                    ],
                    [
                        {
                            key: "getBar",
                            value: function (t) {
                                return u.BARS[t] ? u.BARS[t].toString() : "";
                            },
                        },
                        {
                            key: "correctIndex",
                            value: function (t, e) {
                                if (e === u.SET_A) {
                                    var n = t.shift();
                                    return n < 32 ? n + 64 : n - 32;
                                }
                                return e === u.SET_B
                                    ? t.shift() - 32
                                    : 10 * (t.shift() - 48) + t.shift() - 48;
                            },
                        },
                        {
                            key: "next",
                            value: function (t, n, r) {
                                if (!t.length)
                                    return { result: "", checksum: 0 };
                                var o = void 0,
                                    i = void 0;
                                if (t[0] >= 200) {
                                    i = t.shift() - 105;
                                    var a = u.SWAP[i];
                                    void 0 !== a
                                        ? (o = e.next(t, n + 1, a))
                                        : ((r !== u.SET_A && r !== u.SET_B) ||
                                              i !== u.SHIFT ||
                                              (t[0] =
                                                  r === u.SET_A
                                                      ? t[0] > 95
                                                          ? t[0] - 96
                                                          : t[0]
                                                      : t[0] < 32
                                                      ? t[0] + 96
                                                      : t[0]),
                                          (o = e.next(t, n + 1, r)));
                                } else
                                    (i = e.correctIndex(t, r)),
                                        (o = e.next(t, n + 1, r));
                                var f = i * n;
                                return {
                                    result: e.getBar(i) + o.result,
                                    checksum: f + o.checksum,
                                };
                            },
                        },
                    ]
                ),
                e
            );
        })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.mod10 = function (t) {
                for (var e = 0, n = 0; n < t.length; n++) {
                    var r = parseInt(t[n]);
                    (n + t.length) % 2 == 0
                        ? (e += r)
                        : (e += ((2 * r) % 10) + Math.floor((2 * r) / 10));
                }
                return (10 - (e % 10)) % 10;
            }),
            (e.mod11 = function (t) {
                for (
                    var e = 0, n = [2, 3, 4, 5, 6, 7], r = 0;
                    r < t.length;
                    r++
                ) {
                    var o = parseInt(t[t.length - 1 - r]);
                    e += n[r % n.length] * o;
                }
                return (11 - (e % 11)) % 11;
            });
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r =
            Object.assign ||
            function (t) {
                for (var e = 1; e < arguments.length; e++) {
                    var n = arguments[e];
                    for (var r in n)
                        Object.prototype.hasOwnProperty.call(n, r) &&
                            (t[r] = n[r]);
                }
                return t;
            };
        e.default = function (t, e) {
            return r({}, t, e);
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            o = n(2),
            i = a(n(3));
        function a(t) {
            return t && t.__esModule ? t : { default: t };
        }
        var u = (function (t) {
            function e(t, n) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, e);
                var r = (function (t, e) {
                    if (!t)
                        throw new ReferenceError(
                            "this hasn't been initialised - super() hasn't been called"
                        );
                    return !e ||
                        ("object" != typeof e && "function" != typeof e)
                        ? t
                        : e;
                })(
                    this,
                    (e.__proto__ || Object.getPrototypeOf(e)).call(this, t, n)
                );
                return (
                    (r.fontSize =
                        !n.flat && n.fontSize > 10 * n.width
                            ? 10 * n.width
                            : n.fontSize),
                    (r.guardHeight = n.height + r.fontSize / 2 + n.textMargin),
                    r
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                r(e, [
                    {
                        key: "encode",
                        value: function () {
                            return this.options.flat
                                ? this.encodeFlat()
                                : this.encodeGuarded();
                        },
                    },
                    {
                        key: "leftText",
                        value: function (t, e) {
                            return this.text.substr(t, e);
                        },
                    },
                    {
                        key: "leftEncode",
                        value: function (t, e) {
                            return (0, i.default)(t, e);
                        },
                    },
                    {
                        key: "rightText",
                        value: function (t, e) {
                            return this.text.substr(t, e);
                        },
                    },
                    {
                        key: "rightEncode",
                        value: function (t, e) {
                            return (0, i.default)(t, e);
                        },
                    },
                    {
                        key: "encodeGuarded",
                        value: function () {
                            var t = { fontSize: this.fontSize },
                                e = { height: this.guardHeight };
                            return [
                                { data: o.SIDE_BIN, options: e },
                                {
                                    data: this.leftEncode(),
                                    text: this.leftText(),
                                    options: t,
                                },
                                { data: o.MIDDLE_BIN, options: e },
                                {
                                    data: this.rightEncode(),
                                    text: this.rightText(),
                                    options: t,
                                },
                                { data: o.SIDE_BIN, options: e },
                            ];
                        },
                    },
                    {
                        key: "encodeFlat",
                        value: function () {
                            return {
                                data: [
                                    o.SIDE_BIN,
                                    this.leftEncode(),
                                    o.MIDDLE_BIN,
                                    this.rightEncode(),
                                    o.SIDE_BIN,
                                ].join(""),
                                text: this.text,
                            };
                        },
                    },
                ]),
                e
            );
        })(a(n(0)).default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
            function t(t, e) {
                for (var n = 0; n < e.length; n++) {
                    var r = e[n];
                    (r.enumerable = r.enumerable || !1),
                        (r.configurable = !0),
                        "value" in r && (r.writable = !0),
                        Object.defineProperty(t, r.key, r);
                }
            }
            return function (e, n, r) {
                return n && t(e.prototype, n), r && t(e, r), e;
            };
        })();
        e.checksum = u;
        var o = i(n(3));
        function i(t) {
            return t && t.__esModule ? t : { default: t };
        }
        var a = (function (t) {
            function e(t, n) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, e),
                    -1 !== t.search(/^[0-9]{11}$/) && (t += u(t));
                var r = (function (t, e) {
                    if (!t)
                        throw new ReferenceError(
                            "this hasn't been initialised - super() hasn't been called"
                        );
                    return !e ||
                        ("object" != typeof e && "function" != typeof e)
                        ? t
                        : e;
                })(
                    this,
                    (e.__proto__ || Object.getPrototypeOf(e)).call(this, t, n)
                );
                return (
                    (r.displayValue = n.displayValue),
                    n.fontSize > 10 * n.width
                        ? (r.fontSize = 10 * n.width)
                        : (r.fontSize = n.fontSize),
                    (r.guardHeight = n.height + r.fontSize / 2 + n.textMargin),
                    r
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                r(e, [
                    {
                        key: "valid",
                        value: function () {
                            return (
                                -1 !== this.data.search(/^[0-9]{12}$/) &&
                                this.data[11] == u(this.data)
                            );
                        },
                    },
                    {
                        key: "encode",
                        value: function () {
                            return this.options.flat
                                ? this.flatEncoding()
                                : this.guardedEncoding();
                        },
                    },
                    {
                        key: "flatEncoding",
                        value: function () {
                            var t = "";
                            return (
                                (t += "101"),
                                (t += (0, o.default)(
                                    this.data.substr(0, 6),
                                    "LLLLLL"
                                )),
                                (t += "01010"),
                                (t += (0, o.default)(
                                    this.data.substr(6, 6),
                                    "RRRRRR"
                                )),
                                { data: (t += "101"), text: this.text }
                            );
                        },
                    },
                    {
                        key: "guardedEncoding",
                        value: function () {
                            var t = [];
                            return (
                                this.displayValue &&
                                    t.push({
                                        data: "00000000",
                                        text: this.text.substr(0, 1),
                                        options: {
                                            textAlign: "left",
                                            fontSize: this.fontSize,
                                        },
                                    }),
                                t.push({
                                    data:
                                        "101" +
                                        (0, o.default)(this.data[0], "L"),
                                    options: { height: this.guardHeight },
                                }),
                                t.push({
                                    data: (0, o.default)(
                                        this.data.substr(1, 5),
                                        "LLLLL"
                                    ),
                                    text: this.text.substr(1, 5),
                                    options: { fontSize: this.fontSize },
                                }),
                                t.push({
                                    data: "01010",
                                    options: { height: this.guardHeight },
                                }),
                                t.push({
                                    data: (0, o.default)(
                                        this.data.substr(6, 5),
                                        "RRRRR"
                                    ),
                                    text: this.text.substr(6, 5),
                                    options: { fontSize: this.fontSize },
                                }),
                                t.push({
                                    data:
                                        (0, o.default)(this.data[11], "R") +
                                        "101",
                                    options: { height: this.guardHeight },
                                }),
                                this.displayValue &&
                                    t.push({
                                        data: "00000000",
                                        text: this.text.substr(11, 1),
                                        options: {
                                            textAlign: "right",
                                            fontSize: this.fontSize,
                                        },
                                    }),
                                t
                            );
                        },
                    },
                ]),
                e
            );
        })(i(n(0)).default);
        function u(t) {
            var e,
                n = 0;
            for (e = 1; e < 11; e += 2) n += parseInt(t[e]);
            for (e = 0; e < 11; e += 2) n += 3 * parseInt(t[e]);
            return (10 - (n % 10)) % 10;
        }
        e.default = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(31),
            a = n(0);
        function u(t, e) {
            if (!(t instanceof e))
                throw new TypeError("Cannot call a class as a function");
        }
        function f(t, e) {
            if (!t)
                throw new ReferenceError(
                    "this hasn't been initialised - super() hasn't been called"
                );
            return !e || ("object" != typeof e && "function" != typeof e)
                ? t
                : e;
        }
        var c = (function (t) {
            function e() {
                return (
                    u(this, e),
                    f(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).apply(
                            this,
                            arguments
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "valid",
                        value: function () {
                            return -1 !== this.data.search(/^([0-9]{2})+$/);
                        },
                    },
                    {
                        key: "encode",
                        value: function () {
                            var t = this,
                                e = this.data
                                    .match(/.{2}/g)
                                    .map(function (e) {
                                        return t.encodePair(e);
                                    })
                                    .join("");
                            return {
                                data: i.START_BIN + e + i.END_BIN,
                                text: this.text,
                            };
                        },
                    },
                    {
                        key: "encodePair",
                        value: function (t) {
                            var e = i.BINARIES[t[1]];
                            return i.BINARIES[t[0]]
                                .split("")
                                .map(function (t, n) {
                                    return (
                                        ("1" === t ? "111" : "1") +
                                        ("1" === e[n] ? "000" : "0")
                                    );
                                })
                                .join("");
                        },
                    },
                ]),
                e
            );
        })(((r = a) && r.__esModule ? r : { default: r }).default);
        e.default = c;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.default = function (t) {
                var e = [
                    "width",
                    "height",
                    "textMargin",
                    "fontSize",
                    "margin",
                    "marginTop",
                    "marginBottom",
                    "marginLeft",
                    "marginRight",
                ];
                for (var n in e)
                    e.hasOwnProperty(n) &&
                        ((n = e[n]),
                        "string" == typeof t[n] && (t[n] = parseInt(t[n], 10)));
                "string" == typeof t.displayValue &&
                    (t.displayValue = "false" != t.displayValue);
                return t;
            });
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = {
            width: 2,
            height: 100,
            format: "auto",
            displayValue: !0,
            fontOptions: "",
            font: "monospace",
            text: void 0,
            textAlign: "center",
            textPosition: "bottom",
            textMargin: 2,
            fontSize: 20,
            background: "#ffffff",
            lineColor: "#000000",
            margin: 10,
            marginTop: void 0,
            marginBottom: void 0,
            marginLeft: void 0,
            marginRight: void 0,
            valid: function () {},
        };
        e.default = r;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.getTotalWidthOfEncodings =
                e.calculateEncodingAttributes =
                e.getBarcodePadding =
                e.getEncodingHeight =
                e.getMaximumHeightOfEncodings =
                    void 0);
        var r,
            o = n(7),
            i = (r = o) && r.__esModule ? r : { default: r };
        function a(t, e) {
            return (
                e.height +
                (e.displayValue && t.text.length > 0
                    ? e.fontSize + e.textMargin
                    : 0) +
                e.marginTop +
                e.marginBottom
            );
        }
        function u(t, e, n) {
            if (n.displayValue && e < t) {
                if ("center" == n.textAlign) return Math.floor((t - e) / 2);
                if ("left" == n.textAlign) return 0;
                if ("right" == n.textAlign) return Math.floor(t - e);
            }
            return 0;
        }
        function f(t, e, n) {
            var r;
            if (n) r = n;
            else {
                if ("undefined" == typeof document) return 0;
                r = document.createElement("canvas").getContext("2d");
            }
            r.font = e.fontOptions + " " + e.fontSize + "px " + e.font;
            var o = r.measureText(t);
            return o ? o.width : 0;
        }
        (e.getMaximumHeightOfEncodings = function (t) {
            for (var e = 0, n = 0; n < t.length; n++)
                t[n].height > e && (e = t[n].height);
            return e;
        }),
            (e.getEncodingHeight = a),
            (e.getBarcodePadding = u),
            (e.calculateEncodingAttributes = function (t, e, n) {
                for (var r = 0; r < t.length; r++) {
                    var o,
                        c = t[r],
                        s = (0, i.default)(e, c.options);
                    o = s.displayValue ? f(c.text, s, n) : 0;
                    var l = c.data.length * s.width;
                    (c.width = Math.ceil(Math.max(o, l))),
                        (c.height = a(c, s)),
                        (c.barcodePadding = u(o, l, s));
                }
            }),
            (e.getTotalWidthOfEncodings = function (t) {
                for (var e = 0, n = 0; n < t.length; n++) e += t[n].width;
                return e;
            });
    },
    function (t, e, n) {
        "use strict";
        function r(t, e) {
            if (!(t instanceof e))
                throw new TypeError("Cannot call a class as a function");
        }
        function o(t, e) {
            if (!t)
                throw new ReferenceError(
                    "this hasn't been initialised - super() hasn't been called"
                );
            return !e || ("object" != typeof e && "function" != typeof e)
                ? t
                : e;
        }
        function i(t, e) {
            if ("function" != typeof e && null !== e)
                throw new TypeError(
                    "Super expression must either be null or a function, not " +
                        typeof e
                );
            (t.prototype = Object.create(e && e.prototype, {
                constructor: {
                    value: t,
                    enumerable: !1,
                    writable: !0,
                    configurable: !0,
                },
            })),
                e &&
                    (Object.setPrototypeOf
                        ? Object.setPrototypeOf(t, e)
                        : (t.__proto__ = e));
        }
        Object.defineProperty(e, "__esModule", { value: !0 });
        var a = (function (t) {
                function e(t, n) {
                    r(this, e);
                    var i = o(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(this)
                    );
                    return (
                        (i.name = "InvalidInputException"),
                        (i.symbology = t),
                        (i.input = n),
                        (i.message =
                            '"' +
                            i.input +
                            '" is not a valid input for ' +
                            i.symbology),
                        i
                    );
                }
                return i(e, Error), e;
            })(),
            u = (function (t) {
                function e() {
                    r(this, e);
                    var t = o(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(this)
                    );
                    return (
                        (t.name = "InvalidElementException"),
                        (t.message = "Not supported type to render on"),
                        t
                    );
                }
                return i(e, Error), e;
            })(),
            f = (function (t) {
                function e() {
                    r(this, e);
                    var t = o(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(this)
                    );
                    return (
                        (t.name = "NoElementException"),
                        (t.message = "No element to render on."),
                        t
                    );
                }
                return i(e, Error), e;
            })();
        (e.InvalidInputException = a),
            (e.InvalidElementException = u),
            (e.NoElementException = f);
    },
    function (t, e, n) {
        "use strict";
        var r = p(n(16)),
            o = p(n(7)),
            i = p(n(41)),
            a = p(n(42)),
            u = p(n(43)),
            f = p(n(11)),
            c = p(n(49)),
            s = n(14),
            l = p(n(12));
        function p(t) {
            return t && t.__esModule ? t : { default: t };
        }
        var d = function () {},
            h = function (t, e, n) {
                var r = new d();
                if (void 0 === t)
                    throw Error("No element to render on was provided.");
                return (
                    (r._renderProperties = (0, u.default)(t)),
                    (r._encodings = []),
                    (r._options = l.default),
                    (r._errorHandler = new c.default(r)),
                    void 0 !== e &&
                        ((n = n || {}).format || (n.format = _()),
                        r.options(n)[n.format](e, n).render()),
                    r
                );
            };
        for (var y in ((h.getModule = function (t) {
            return r.default[t];
        }),
        r.default))
            r.default.hasOwnProperty(y) && b(r.default, y);
        function b(t, e) {
            d.prototype[e] =
                d.prototype[e.toUpperCase()] =
                d.prototype[e.toLowerCase()] =
                    function (n, r) {
                        var i = this;
                        return i._errorHandler.wrapBarcodeCall(function () {
                            r.text = void 0 === r.text ? void 0 : "" + r.text;
                            var a = (0, o.default)(i._options, r);
                            a = (0, f.default)(a);
                            var u = t[e],
                                c = v(n, u, a);
                            return i._encodings.push(c), i;
                        });
                    };
        }
        function v(t, e, n) {
            var r = new e((t = "" + t), n);
            if (!r.valid())
                throw new s.InvalidInputException(r.constructor.name, t);
            var a = r.encode();
            a = (0, i.default)(a);
            for (var u = 0; u < a.length; u++)
                a[u].options = (0, o.default)(n, a[u].options);
            return a;
        }
        function _() {
            return r.default.CODE128 ? "CODE128" : Object.keys(r.default)[0];
        }
        function g(t, e, n) {
            e = (0, i.default)(e);
            for (var r = 0; r < e.length; r++)
                (e[r].options = (0, o.default)(n, e[r].options)),
                    (0, a.default)(e[r].options);
            (0, a.default)(n),
                new (0, t.renderer)(t.element, e, n).render(),
                t.afterRender && t.afterRender();
        }
        (d.prototype.options = function (t) {
            return (this._options = (0, o.default)(this._options, t)), this;
        }),
            (d.prototype.blank = function (t) {
                var e = new Array(t + 1).join("0");
                return this._encodings.push({ data: e }), this;
            }),
            (d.prototype.init = function () {
                var t;
                if (this._renderProperties)
                    for (var e in (Array.isArray(this._renderProperties) ||
                        (this._renderProperties = [this._renderProperties]),
                    this._renderProperties)) {
                        t = this._renderProperties[e];
                        var n = (0, o.default)(this._options, t.options);
                        "auto" == n.format && (n.format = _()),
                            this._errorHandler.wrapBarcodeCall(function () {
                                var e = v(
                                    n.value,
                                    r.default[n.format.toUpperCase()],
                                    n
                                );
                                g(t, e, n);
                            });
                    }
            }),
            (d.prototype.render = function () {
                if (!this._renderProperties) throw new s.NoElementException();
                if (Array.isArray(this._renderProperties))
                    for (var t = 0; t < this._renderProperties.length; t++)
                        g(
                            this._renderProperties[t],
                            this._encodings,
                            this._options
                        );
                else g(this._renderProperties, this._encodings, this._options);
                return this;
            }),
            (d.prototype._defaults = l.default),
            "undefined" != typeof window && (window.JsBarcode = h),
            "undefined" != typeof jQuery &&
                (jQuery.fn.JsBarcode = function (t, e) {
                    var n = [];
                    return (
                        jQuery(this).each(function () {
                            n.push(this);
                        }),
                        h(n, t, e)
                    );
                }),
            (t.exports = h);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = n(17),
            o = n(18),
            i = n(24),
            a = n(30),
            u = n(33),
            f = n(38),
            c = n(39),
            s = n(40);
        e.default = {
            CODE39: r.CODE39,
            CODE128: o.CODE128,
            CODE128A: o.CODE128A,
            CODE128B: o.CODE128B,
            CODE128C: o.CODE128C,
            EAN13: i.EAN13,
            EAN8: i.EAN8,
            EAN5: i.EAN5,
            EAN2: i.EAN2,
            UPC: i.UPC,
            UPCE: i.UPCE,
            ITF14: a.ITF14,
            ITF: a.ITF,
            MSI: u.MSI,
            MSI10: u.MSI10,
            MSI11: u.MSI11,
            MSI1010: u.MSI1010,
            MSI1110: u.MSI1110,
            pharmacode: f.pharmacode,
            codabar: c.codabar,
            GenericBarcode: s.GenericBarcode,
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.CODE39 = void 0);
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0);
        var a = (function (t) {
                function e(t, n) {
                    return (
                        (function (t, e) {
                            if (!(t instanceof e))
                                throw new TypeError(
                                    "Cannot call a class as a function"
                                );
                        })(this, e),
                        (t = t.toUpperCase()),
                        n.mod43 &&
                            (t += (function (t) {
                                return u[t];
                            })(
                                (function (t) {
                                    for (var e = 0, n = 0; n < t.length; n++)
                                        e += s(t[n]);
                                    return (e %= 43);
                                })(t)
                            )),
                        (function (t, e) {
                            if (!t)
                                throw new ReferenceError(
                                    "this hasn't been initialised - super() hasn't been called"
                                );
                            return !e ||
                                ("object" != typeof e && "function" != typeof e)
                                ? t
                                : e;
                        })(
                            this,
                            (e.__proto__ || Object.getPrototypeOf(e)).call(
                                this,
                                t,
                                n
                            )
                        )
                    );
                }
                return (
                    (function (t, e) {
                        if ("function" != typeof e && null !== e)
                            throw new TypeError(
                                "Super expression must either be null or a function, not " +
                                    typeof e
                            );
                        (t.prototype = Object.create(e && e.prototype, {
                            constructor: {
                                value: t,
                                enumerable: !1,
                                writable: !0,
                                configurable: !0,
                            },
                        })),
                            e &&
                                (Object.setPrototypeOf
                                    ? Object.setPrototypeOf(t, e)
                                    : (t.__proto__ = e));
                    })(e, t),
                    o(e, [
                        {
                            key: "encode",
                            value: function () {
                                for (
                                    var t = c("*"), e = 0;
                                    e < this.data.length;
                                    e++
                                )
                                    t += c(this.data[e]) + "0";
                                return { data: (t += c("*")), text: this.text };
                            },
                        },
                        {
                            key: "valid",
                            value: function () {
                                return (
                                    -1 !==
                                    this.data.search(
                                        /^[0-9A-Z\-\.\ \$\/\+\%]+$/
                                    )
                                );
                            },
                        },
                    ]),
                    e
                );
            })(((r = i) && r.__esModule ? r : { default: r }).default),
            u = [
                "0",
                "1",
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9",
                "A",
                "B",
                "C",
                "D",
                "E",
                "F",
                "G",
                "H",
                "I",
                "J",
                "K",
                "L",
                "M",
                "N",
                "O",
                "P",
                "Q",
                "R",
                "S",
                "T",
                "U",
                "V",
                "W",
                "X",
                "Y",
                "Z",
                "-",
                ".",
                " ",
                "$",
                "/",
                "+",
                "%",
                "*",
            ],
            f = [
                20957, 29783, 23639, 30485, 20951, 29813, 23669, 20855, 29789,
                23645, 29975, 23831, 30533, 22295, 30149, 24005, 21623, 29981,
                23837, 22301, 30023, 23879, 30545, 22343, 30161, 24017, 21959,
                30065, 23921, 22385, 29015, 18263, 29141, 17879, 29045, 18293,
                17783, 29021, 18269, 17477, 17489, 17681, 20753, 35770,
            ];
        function c(t) {
            return (function (t) {
                return f[t].toString(2);
            })(s(t));
        }
        function s(t) {
            return u.indexOf(t);
        }
        e.CODE39 = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.CODE128C = e.CODE128B = e.CODE128A = e.CODE128 = void 0);
        var r = u(n(19)),
            o = u(n(21)),
            i = u(n(22)),
            a = u(n(23));
        function u(t) {
            return t && t.__esModule ? t : { default: t };
        }
        (e.CODE128 = r.default),
            (e.CODE128A = o.default),
            (e.CODE128B = i.default),
            (e.CODE128C = a.default);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = i(n(5)),
            o = i(n(20));
        function i(t) {
            return t && t.__esModule ? t : { default: t };
        }
        function a(t, e) {
            if (!t)
                throw new ReferenceError(
                    "this hasn't been initialised - super() hasn't been called"
                );
            return !e || ("object" != typeof e && "function" != typeof e)
                ? t
                : e;
        }
        var u = (function (t) {
            function e(t, n) {
                if (
                    ((function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    /^[\x00-\x7F\xC8-\xD3]+$/.test(t))
                )
                    var r = a(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            (0, o.default)(t),
                            n
                        )
                    );
                else
                    r = a(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    );
                return a(r);
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                e
            );
        })(r.default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = n(1),
            o = function (t) {
                return t.match(new RegExp("^" + r.A_CHARS + "*"))[0].length;
            },
            i = function (t) {
                return t.match(new RegExp("^" + r.B_CHARS + "*"))[0].length;
            },
            a = function (t) {
                return t.match(new RegExp("^" + r.C_CHARS + "*"))[0];
            };
        function u(t, e) {
            var n = e ? r.A_CHARS : r.B_CHARS,
                o = t.match(
                    new RegExp("^(" + n + "+?)(([0-9]{2}){2,})([^0-9]|$)")
                );
            if (o)
                return (
                    o[1] +
                    String.fromCharCode(204) +
                    f(t.substring(o[1].length))
                );
            var i = t.match(new RegExp("^" + n + "+"))[0];
            return i.length === t.length
                ? t
                : i +
                      String.fromCharCode(e ? 205 : 206) +
                      u(t.substring(i.length), !e);
        }
        function f(t) {
            var e = a(t),
                n = e.length;
            if (n === t.length) return t;
            t = t.substring(n);
            var r = o(t) >= i(t);
            return e + String.fromCharCode(r ? 206 : 205) + u(t, r);
        }
        e.default = function (t) {
            var e = void 0;
            if (a(t).length >= 2) e = r.C_START_CHAR + f(t);
            else {
                var n = o(t) > i(t);
                e = (n ? r.A_START_CHAR : r.B_START_CHAR) + u(t, n);
            }
            return e.replace(/[\xCD\xCE]([^])[\xCD\xCE]/, function (t, e) {
                return String.fromCharCode(203) + e;
            });
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(5),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(1);
        var f = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            u.A_START_CHAR + t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "valid",
                        value: function () {
                            return new RegExp("^" + u.A_CHARS + "+$").test(
                                this.data
                            );
                        },
                    },
                ]),
                e
            );
        })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(5),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(1);
        var f = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            u.B_START_CHAR + t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "valid",
                        value: function () {
                            return new RegExp("^" + u.B_CHARS + "+$").test(
                                this.data
                            );
                        },
                    },
                ]),
                e
            );
        })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(5),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(1);
        var f = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            u.C_START_CHAR + t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "valid",
                        value: function () {
                            return new RegExp("^" + u.C_CHARS + "+$").test(
                                this.data
                            );
                        },
                    },
                ]),
                e
            );
        })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.UPCE = e.UPC = e.EAN2 = e.EAN5 = e.EAN8 = e.EAN13 = void 0);
        var r = c(n(25)),
            o = c(n(26)),
            i = c(n(27)),
            a = c(n(28)),
            u = c(n(9)),
            f = c(n(29));
        function c(t) {
            return t && t.__esModule ? t : { default: t };
        }
        (e.EAN13 = r.default),
            (e.EAN8 = o.default),
            (e.EAN5 = i.default),
            (e.EAN2 = a.default),
            (e.UPC = u.default),
            (e.UPCE = f.default);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = function t(e, n, r) {
                null === e && (e = Function.prototype);
                var o = Object.getOwnPropertyDescriptor(e, n);
                if (void 0 === o) {
                    var i = Object.getPrototypeOf(e);
                    return null === i ? void 0 : t(i, n, r);
                }
                if ("value" in o) return o.value;
                var a = o.get;
                return void 0 !== a ? a.call(r) : void 0;
            },
            a = n(2),
            u = n(8),
            f = (r = u) && r.__esModule ? r : { default: r };
        var c = function (t) {
                return (
                    (10 -
                        (t
                            .substr(0, 12)
                            .split("")
                            .map(function (t) {
                                return +t;
                            })
                            .reduce(function (t, e, n) {
                                return n % 2 ? t + 3 * e : t + e;
                            }, 0) %
                            10)) %
                    10
                );
            },
            s = (function (t) {
                function e(t, n) {
                    !(function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                        -1 !== t.search(/^[0-9]{12}$/) && (t += c(t));
                    var r = (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    );
                    return (r.lastChar = n.lastChar), r;
                }
                return (
                    (function (t, e) {
                        if ("function" != typeof e && null !== e)
                            throw new TypeError(
                                "Super expression must either be null or a function, not " +
                                    typeof e
                            );
                        (t.prototype = Object.create(e && e.prototype, {
                            constructor: {
                                value: t,
                                enumerable: !1,
                                writable: !0,
                                configurable: !0,
                            },
                        })),
                            e &&
                                (Object.setPrototypeOf
                                    ? Object.setPrototypeOf(t, e)
                                    : (t.__proto__ = e));
                    })(e, t),
                    o(e, [
                        {
                            key: "valid",
                            value: function () {
                                return (
                                    -1 !== this.data.search(/^[0-9]{13}$/) &&
                                    +this.data[12] === c(this.data)
                                );
                            },
                        },
                        {
                            key: "leftText",
                            value: function () {
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "leftText",
                                    this
                                ).call(this, 1, 6);
                            },
                        },
                        {
                            key: "leftEncode",
                            value: function () {
                                var t = this.data.substr(1, 6),
                                    n = a.EAN13_STRUCTURE[this.data[0]];
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "leftEncode",
                                    this
                                ).call(this, t, n);
                            },
                        },
                        {
                            key: "rightText",
                            value: function () {
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "rightText",
                                    this
                                ).call(this, 7, 6);
                            },
                        },
                        {
                            key: "rightEncode",
                            value: function () {
                                var t = this.data.substr(7, 6);
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "rightEncode",
                                    this
                                ).call(this, t, "RRRRRR");
                            },
                        },
                        {
                            key: "encodeGuarded",
                            value: function () {
                                var t = i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "encodeGuarded",
                                    this
                                ).call(this);
                                return (
                                    this.options.displayValue &&
                                        (t.unshift({
                                            data: "000000000000",
                                            text: this.text.substr(0, 1),
                                            options: {
                                                textAlign: "left",
                                                fontSize: this.fontSize,
                                            },
                                        }),
                                        this.options.lastChar &&
                                            (t.push({ data: "00" }),
                                            t.push({
                                                data: "00000",
                                                text: this.options.lastChar,
                                                options: {
                                                    fontSize: this.fontSize,
                                                },
                                            }))),
                                    t
                                );
                            },
                        },
                    ]),
                    e
                );
            })(f.default);
        e.default = s;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = function t(e, n, r) {
                null === e && (e = Function.prototype);
                var o = Object.getOwnPropertyDescriptor(e, n);
                if (void 0 === o) {
                    var i = Object.getPrototypeOf(e);
                    return null === i ? void 0 : t(i, n, r);
                }
                if ("value" in o) return o.value;
                var a = o.get;
                return void 0 !== a ? a.call(r) : void 0;
            },
            a = n(8),
            u = (r = a) && r.__esModule ? r : { default: r };
        var f = function (t) {
                return (
                    (10 -
                        (t
                            .substr(0, 7)
                            .split("")
                            .map(function (t) {
                                return +t;
                            })
                            .reduce(function (t, e, n) {
                                return n % 2 ? t + e : t + 3 * e;
                            }, 0) %
                            10)) %
                    10
                );
            },
            c = (function (t) {
                function e(t, n) {
                    return (
                        (function (t, e) {
                            if (!(t instanceof e))
                                throw new TypeError(
                                    "Cannot call a class as a function"
                                );
                        })(this, e),
                        -1 !== t.search(/^[0-9]{7}$/) && (t += f(t)),
                        (function (t, e) {
                            if (!t)
                                throw new ReferenceError(
                                    "this hasn't been initialised - super() hasn't been called"
                                );
                            return !e ||
                                ("object" != typeof e && "function" != typeof e)
                                ? t
                                : e;
                        })(
                            this,
                            (e.__proto__ || Object.getPrototypeOf(e)).call(
                                this,
                                t,
                                n
                            )
                        )
                    );
                }
                return (
                    (function (t, e) {
                        if ("function" != typeof e && null !== e)
                            throw new TypeError(
                                "Super expression must either be null or a function, not " +
                                    typeof e
                            );
                        (t.prototype = Object.create(e && e.prototype, {
                            constructor: {
                                value: t,
                                enumerable: !1,
                                writable: !0,
                                configurable: !0,
                            },
                        })),
                            e &&
                                (Object.setPrototypeOf
                                    ? Object.setPrototypeOf(t, e)
                                    : (t.__proto__ = e));
                    })(e, t),
                    o(e, [
                        {
                            key: "valid",
                            value: function () {
                                return (
                                    -1 !== this.data.search(/^[0-9]{8}$/) &&
                                    +this.data[7] === f(this.data)
                                );
                            },
                        },
                        {
                            key: "leftText",
                            value: function () {
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "leftText",
                                    this
                                ).call(this, 0, 4);
                            },
                        },
                        {
                            key: "leftEncode",
                            value: function () {
                                var t = this.data.substr(0, 4);
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "leftEncode",
                                    this
                                ).call(this, t, "LLLL");
                            },
                        },
                        {
                            key: "rightText",
                            value: function () {
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "rightText",
                                    this
                                ).call(this, 4, 4);
                            },
                        },
                        {
                            key: "rightEncode",
                            value: function () {
                                var t = this.data.substr(4, 4);
                                return i(
                                    e.prototype.__proto__ ||
                                        Object.getPrototypeOf(e.prototype),
                                    "rightEncode",
                                    this
                                ).call(this, t, "RRRR");
                            },
                        },
                    ]),
                    e
                );
            })(u.default);
        e.default = c;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            o = n(2),
            i = u(n(3)),
            a = u(n(0));
        function u(t) {
            return t && t.__esModule ? t : { default: t };
        }
        var f = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                r(e, [
                    {
                        key: "valid",
                        value: function () {
                            return -1 !== this.data.search(/^[0-9]{5}$/);
                        },
                    },
                    {
                        key: "encode",
                        value: function () {
                            var t,
                                e =
                                    o.EAN5_STRUCTURE[
                                        ((t = this.data),
                                        t
                                            .split("")
                                            .map(function (t) {
                                                return +t;
                                            })
                                            .reduce(function (t, e, n) {
                                                return n % 2
                                                    ? t + 9 * e
                                                    : t + 3 * e;
                                            }, 0) % 10)
                                    ];
                            return {
                                data:
                                    "1011" + (0, i.default)(this.data, e, "01"),
                                text: this.text,
                            };
                        },
                    },
                ]),
                e
            );
        })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            o = n(2),
            i = a(n(3));
        function a(t) {
            return t && t.__esModule ? t : { default: t };
        }
        var u = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                r(e, [
                    {
                        key: "valid",
                        value: function () {
                            return -1 !== this.data.search(/^[0-9]{2}$/);
                        },
                    },
                    {
                        key: "encode",
                        value: function () {
                            var t = o.EAN2_STRUCTURE[parseInt(this.data) % 4];
                            return {
                                data:
                                    "1011" + (0, i.default)(this.data, t, "01"),
                                text: this.text,
                            };
                        },
                    },
                ]),
                e
            );
        })(a(n(0)).default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            o = u(n(3)),
            i = u(n(0)),
            a = n(9);
        function u(t) {
            return t && t.__esModule ? t : { default: t };
        }
        function f(t, e) {
            if (!t)
                throw new ReferenceError(
                    "this hasn't been initialised - super() hasn't been called"
                );
            return !e || ("object" != typeof e && "function" != typeof e)
                ? t
                : e;
        }
        var c = [
                "XX00000XXX",
                "XX10000XXX",
                "XX20000XXX",
                "XXX00000XX",
                "XXXX00000X",
                "XXXXX00005",
                "XXXXX00006",
                "XXXXX00007",
                "XXXXX00008",
                "XXXXX00009",
            ],
            s = [
                ["EEEOOO", "OOOEEE"],
                ["EEOEOO", "OOEOEE"],
                ["EEOOEO", "OOEEOE"],
                ["EEOOOE", "OOEEEO"],
                ["EOEEOO", "OEOOEE"],
                ["EOOEEO", "OEEOOE"],
                ["EOOOEE", "OEEEOO"],
                ["EOEOEO", "OEOEOE"],
                ["EOEOOE", "OEOEEO"],
                ["EOOEOE", "OEEOEO"],
            ],
            l = (function (t) {
                function e(t, n) {
                    !(function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e);
                    var r = f(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    );
                    if (((r.isValid = !1), -1 !== t.search(/^[0-9]{6}$/)))
                        (r.middleDigits = t),
                            (r.upcA = p(t, "0")),
                            (r.text =
                                n.text ||
                                "" + r.upcA[0] + t + r.upcA[r.upcA.length - 1]),
                            (r.isValid = !0);
                    else {
                        if (-1 === t.search(/^[01][0-9]{7}$/)) return f(r);
                        if (
                            ((r.middleDigits = t.substring(1, t.length - 1)),
                            (r.upcA = p(r.middleDigits, t[0])),
                            r.upcA[r.upcA.length - 1] !== t[t.length - 1])
                        )
                            return f(r);
                        r.isValid = !0;
                    }
                    return (
                        (r.displayValue = n.displayValue),
                        n.fontSize > 10 * n.width
                            ? (r.fontSize = 10 * n.width)
                            : (r.fontSize = n.fontSize),
                        (r.guardHeight =
                            n.height + r.fontSize / 2 + n.textMargin),
                        r
                    );
                }
                return (
                    (function (t, e) {
                        if ("function" != typeof e && null !== e)
                            throw new TypeError(
                                "Super expression must either be null or a function, not " +
                                    typeof e
                            );
                        (t.prototype = Object.create(e && e.prototype, {
                            constructor: {
                                value: t,
                                enumerable: !1,
                                writable: !0,
                                configurable: !0,
                            },
                        })),
                            e &&
                                (Object.setPrototypeOf
                                    ? Object.setPrototypeOf(t, e)
                                    : (t.__proto__ = e));
                    })(e, t),
                    r(e, [
                        {
                            key: "valid",
                            value: function () {
                                return this.isValid;
                            },
                        },
                        {
                            key: "encode",
                            value: function () {
                                return this.options.flat
                                    ? this.flatEncoding()
                                    : this.guardedEncoding();
                            },
                        },
                        {
                            key: "flatEncoding",
                            value: function () {
                                var t = "";
                                return (
                                    (t += "101"),
                                    (t += this.encodeMiddleDigits()),
                                    { data: (t += "010101"), text: this.text }
                                );
                            },
                        },
                        {
                            key: "guardedEncoding",
                            value: function () {
                                var t = [];
                                return (
                                    this.displayValue &&
                                        t.push({
                                            data: "00000000",
                                            text: this.text[0],
                                            options: {
                                                textAlign: "left",
                                                fontSize: this.fontSize,
                                            },
                                        }),
                                    t.push({
                                        data: "101",
                                        options: { height: this.guardHeight },
                                    }),
                                    t.push({
                                        data: this.encodeMiddleDigits(),
                                        text: this.text.substring(1, 7),
                                        options: { fontSize: this.fontSize },
                                    }),
                                    t.push({
                                        data: "010101",
                                        options: { height: this.guardHeight },
                                    }),
                                    this.displayValue &&
                                        t.push({
                                            data: "00000000",
                                            text: this.text[7],
                                            options: {
                                                textAlign: "right",
                                                fontSize: this.fontSize,
                                            },
                                        }),
                                    t
                                );
                            },
                        },
                        {
                            key: "encodeMiddleDigits",
                            value: function () {
                                var t = this.upcA[0],
                                    e = this.upcA[this.upcA.length - 1],
                                    n = s[parseInt(e)][parseInt(t)];
                                return (0, o.default)(this.middleDigits, n);
                            },
                        },
                    ]),
                    e
                );
            })(i.default);
        function p(t, e) {
            for (
                var n = parseInt(t[t.length - 1]),
                    r = c[n],
                    o = "",
                    i = 0,
                    u = 0;
                u < r.length;
                u++
            ) {
                var f = r[u];
                o += "X" === f ? t[i++] : f;
            }
            return "" + (o = "" + e + o) + (0, a.checksum)(o);
        }
        e.default = l;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.ITF14 = e.ITF = void 0);
        var r = i(n(10)),
            o = i(n(32));
        function i(t) {
            return t && t.__esModule ? t : { default: t };
        }
        (e.ITF = r.default), (e.ITF14 = o.default);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        (e.START_BIN = "1010"),
            (e.END_BIN = "11101"),
            (e.BINARIES = [
                "00110",
                "10001",
                "01001",
                "11000",
                "00101",
                "10100",
                "01100",
                "00011",
                "10010",
                "01010",
            ]);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(10),
            a = (r = i) && r.__esModule ? r : { default: r };
        var u = function (t) {
                var e = t
                    .substr(0, 13)
                    .split("")
                    .map(function (t) {
                        return parseInt(t, 10);
                    })
                    .reduce(function (t, e, n) {
                        return t + e * (3 - (n % 2) * 2);
                    }, 0);
                return 10 * Math.ceil(e / 10) - e;
            },
            f = (function (t) {
                function e(t, n) {
                    return (
                        (function (t, e) {
                            if (!(t instanceof e))
                                throw new TypeError(
                                    "Cannot call a class as a function"
                                );
                        })(this, e),
                        -1 !== t.search(/^[0-9]{13}$/) && (t += u(t)),
                        (function (t, e) {
                            if (!t)
                                throw new ReferenceError(
                                    "this hasn't been initialised - super() hasn't been called"
                                );
                            return !e ||
                                ("object" != typeof e && "function" != typeof e)
                                ? t
                                : e;
                        })(
                            this,
                            (e.__proto__ || Object.getPrototypeOf(e)).call(
                                this,
                                t,
                                n
                            )
                        )
                    );
                }
                return (
                    (function (t, e) {
                        if ("function" != typeof e && null !== e)
                            throw new TypeError(
                                "Super expression must either be null or a function, not " +
                                    typeof e
                            );
                        (t.prototype = Object.create(e && e.prototype, {
                            constructor: {
                                value: t,
                                enumerable: !1,
                                writable: !0,
                                configurable: !0,
                            },
                        })),
                            e &&
                                (Object.setPrototypeOf
                                    ? Object.setPrototypeOf(t, e)
                                    : (t.__proto__ = e));
                    })(e, t),
                    o(e, [
                        {
                            key: "valid",
                            value: function () {
                                return (
                                    -1 !== this.data.search(/^[0-9]{14}$/) &&
                                    +this.data[13] === u(this.data)
                                );
                            },
                        },
                    ]),
                    e
                );
            })(a.default);
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.MSI1110 = e.MSI1010 = e.MSI11 = e.MSI10 = e.MSI = void 0);
        var r = f(n(4)),
            o = f(n(34)),
            i = f(n(35)),
            a = f(n(36)),
            u = f(n(37));
        function f(t) {
            return t && t.__esModule ? t : { default: t };
        }
        (e.MSI = r.default),
            (e.MSI10 = o.default),
            (e.MSI11 = i.default),
            (e.MSI1010 = a.default),
            (e.MSI1110 = u.default);
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = n(4),
            i = (r = o) && r.__esModule ? r : { default: r },
            a = n(6);
        var u = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t + (0, a.mod10)(t),
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                e
            );
        })(i.default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = n(4),
            i = (r = o) && r.__esModule ? r : { default: r },
            a = n(6);
        var u = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t + (0, a.mod11)(t),
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                e
            );
        })(i.default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = n(4),
            i = (r = o) && r.__esModule ? r : { default: r },
            a = n(6);
        var u = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (t += (0, a.mod10)(t)),
                    (t += (0, a.mod10)(t)),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                e
            );
        })(i.default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = n(4),
            i = (r = o) && r.__esModule ? r : { default: r },
            a = n(6);
        var u = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (t += (0, a.mod11)(t)),
                    (t += (0, a.mod10)(t)),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                e
            );
        })(i.default);
        e.default = u;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.pharmacode = void 0);
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0);
        var a = (function (t) {
            function e(t, n) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, e);
                var r = (function (t, e) {
                    if (!t)
                        throw new ReferenceError(
                            "this hasn't been initialised - super() hasn't been called"
                        );
                    return !e ||
                        ("object" != typeof e && "function" != typeof e)
                        ? t
                        : e;
                })(
                    this,
                    (e.__proto__ || Object.getPrototypeOf(e)).call(this, t, n)
                );
                return (r.number = parseInt(t, 10)), r;
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "encode",
                        value: function () {
                            for (
                                var t = this.number, e = "";
                                !isNaN(t) && 0 != t;

                            )
                                t % 2 == 0
                                    ? ((e = "11100" + e), (t = (t - 2) / 2))
                                    : ((e = "100" + e), (t = (t - 1) / 2));
                            return {
                                data: (e = e.slice(0, -2)),
                                text: this.text,
                            };
                        },
                    },
                    {
                        key: "valid",
                        value: function () {
                            return this.number >= 3 && this.number <= 131070;
                        },
                    },
                ]),
                e
            );
        })(((r = i) && r.__esModule ? r : { default: r }).default);
        e.pharmacode = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.codabar = void 0);
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0);
        var a = (function (t) {
            function e(t, n) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, e),
                    0 === t.search(/^[0-9\-\$\:\.\+\/]+$/) &&
                        (t = "A" + t + "A");
                var r = (function (t, e) {
                    if (!t)
                        throw new ReferenceError(
                            "this hasn't been initialised - super() hasn't been called"
                        );
                    return !e ||
                        ("object" != typeof e && "function" != typeof e)
                        ? t
                        : e;
                })(
                    this,
                    (e.__proto__ || Object.getPrototypeOf(e)).call(
                        this,
                        t.toUpperCase(),
                        n
                    )
                );
                return (
                    (r.text = r.options.text || r.text.replace(/[A-D]/g, "")), r
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "valid",
                        value: function () {
                            return (
                                -1 !==
                                this.data.search(
                                    /^[A-D][0-9\-\$\:\.\+\/]+[A-D]$/
                                )
                            );
                        },
                    },
                    {
                        key: "encode",
                        value: function () {
                            for (
                                var t = [], e = this.getEncodings(), n = 0;
                                n < this.data.length;
                                n++
                            )
                                t.push(e[this.data.charAt(n)]),
                                    n !== this.data.length - 1 && t.push("0");
                            return { text: this.text, data: t.join("") };
                        },
                    },
                    {
                        key: "getEncodings",
                        value: function () {
                            return {
                                0: "101010011",
                                1: "101011001",
                                2: "101001011",
                                3: "110010101",
                                4: "101101001",
                                5: "110101001",
                                6: "100101011",
                                7: "100101101",
                                8: "100110101",
                                9: "110100101",
                                "-": "101001101",
                                $: "101100101",
                                ":": "1101011011",
                                "/": "1101101011",
                                ".": "1101101101",
                                "+": "1011011011",
                                A: "1011001001",
                                B: "1001001011",
                                C: "1010010011",
                                D: "1010011001",
                            };
                        },
                    },
                ]),
                e
            );
        })(((r = i) && r.__esModule ? r : { default: r }).default);
        e.codabar = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.GenericBarcode = void 0);
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(0);
        var a = (function (t) {
            function e(t, n) {
                return (
                    (function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, e),
                    (function (t, e) {
                        if (!t)
                            throw new ReferenceError(
                                "this hasn't been initialised - super() hasn't been called"
                            );
                        return !e ||
                            ("object" != typeof e && "function" != typeof e)
                            ? t
                            : e;
                    })(
                        this,
                        (e.__proto__ || Object.getPrototypeOf(e)).call(
                            this,
                            t,
                            n
                        )
                    )
                );
            }
            return (
                (function (t, e) {
                    if ("function" != typeof e && null !== e)
                        throw new TypeError(
                            "Super expression must either be null or a function, not " +
                                typeof e
                        );
                    (t.prototype = Object.create(e && e.prototype, {
                        constructor: {
                            value: t,
                            enumerable: !1,
                            writable: !0,
                            configurable: !0,
                        },
                    })),
                        e &&
                            (Object.setPrototypeOf
                                ? Object.setPrototypeOf(t, e)
                                : (t.__proto__ = e));
                })(e, t),
                o(e, [
                    {
                        key: "encode",
                        value: function () {
                            return {
                                data: "10101010101010101010101010101010101010101",
                                text: this.text,
                            };
                        },
                    },
                    {
                        key: "valid",
                        value: function () {
                            return !0;
                        },
                    },
                ]),
                e
            );
        })(((r = i) && r.__esModule ? r : { default: r }).default);
        e.GenericBarcode = a;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.default = function (t) {
                var e = [];
                return (
                    (function t(n) {
                        if (Array.isArray(n))
                            for (var r = 0; r < n.length; r++) t(n[r]);
                        else
                            (n.text = n.text || ""),
                                (n.data = n.data || ""),
                                e.push(n);
                    })(t),
                    e
                );
            });
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 }),
            (e.default = function (t) {
                return (
                    (t.marginTop = t.marginTop || t.margin),
                    (t.marginBottom = t.marginBottom || t.margin),
                    (t.marginRight = t.marginRight || t.margin),
                    (t.marginLeft = t.marginLeft || t.margin),
                    t
                );
            });
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r =
                "function" == typeof Symbol &&
                "symbol" == typeof Symbol.iterator
                    ? function (t) {
                          return typeof t;
                      }
                    : function (t) {
                          return t &&
                              "function" == typeof Symbol &&
                              t.constructor === Symbol &&
                              t !== Symbol.prototype
                              ? "symbol"
                              : typeof t;
                      },
            o = u(n(44)),
            i = u(n(45)),
            a = n(14);
        function u(t) {
            return t && t.__esModule ? t : { default: t };
        }
        function f(t) {
            if ("string" == typeof t)
                return (function (t) {
                    var e = document.querySelectorAll(t);
                    if (0 === e.length) return;
                    for (var n = [], r = 0; r < e.length; r++) n.push(f(e[r]));
                    return n;
                })(t);
            if (Array.isArray(t)) {
                for (var e = [], n = 0; n < t.length; n++) e.push(f(t[n]));
                return e;
            }
            if (
                "undefined" != typeof HTMLCanvasElement &&
                t instanceof HTMLImageElement
            )
                return (
                    (u = t),
                    {
                        element: (c = document.createElement("canvas")),
                        options: (0, o.default)(u),
                        renderer: i.default.CanvasRenderer,
                        afterRender: function () {
                            u.setAttribute("src", c.toDataURL());
                        },
                    }
                );
            if (
                (t && t.nodeName && "svg" === t.nodeName.toLowerCase()) ||
                ("undefined" != typeof SVGElement && t instanceof SVGElement)
            )
                return {
                    element: t,
                    options: (0, o.default)(t),
                    renderer: i.default.SVGRenderer,
                };
            if (
                "undefined" != typeof HTMLCanvasElement &&
                t instanceof HTMLCanvasElement
            )
                return {
                    element: t,
                    options: (0, o.default)(t),
                    renderer: i.default.CanvasRenderer,
                };
            if (t && t.getContext)
                return { element: t, renderer: i.default.CanvasRenderer };
            if (
                t &&
                "object" === (void 0 === t ? "undefined" : r(t)) &&
                !t.nodeName
            )
                return { element: t, renderer: i.default.ObjectRenderer };
            throw new a.InvalidElementException();
            var u, c;
        }
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = i(n(11)),
            o = i(n(12));
        function i(t) {
            return t && t.__esModule ? t : { default: t };
        }
        e.default = function (t) {
            var e = {};
            for (var n in o.default)
                o.default.hasOwnProperty(n) &&
                    (t.hasAttribute("jsbarcode-" + n.toLowerCase()) &&
                        (e[n] = t.getAttribute("jsbarcode-" + n.toLowerCase())),
                    t.hasAttribute("data-" + n.toLowerCase()) &&
                        (e[n] = t.getAttribute("data-" + n.toLowerCase())));
            return (
                (e.value =
                    t.getAttribute("jsbarcode-value") ||
                    t.getAttribute("data-value")),
                (e = (0, r.default)(e))
            );
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = a(n(46)),
            o = a(n(47)),
            i = a(n(48));
        function a(t) {
            return t && t.__esModule ? t : { default: t };
        }
        e.default = {
            CanvasRenderer: r.default,
            SVGRenderer: o.default,
            ObjectRenderer: i.default,
        };
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(7),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(13);
        var f = (function () {
            function t(e, n, r) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, t),
                    (this.canvas = e),
                    (this.encodings = n),
                    (this.options = r);
            }
            return (
                o(t, [
                    {
                        key: "render",
                        value: function () {
                            if (!this.canvas.getContext)
                                throw new Error(
                                    "The browser does not support canvas."
                                );
                            this.prepareCanvas();
                            for (var t = 0; t < this.encodings.length; t++) {
                                var e = (0, a.default)(
                                    this.options,
                                    this.encodings[t].options
                                );
                                this.drawCanvasBarcode(e, this.encodings[t]),
                                    this.drawCanvasText(e, this.encodings[t]),
                                    this.moveCanvasDrawing(this.encodings[t]);
                            }
                            this.restoreCanvas();
                        },
                    },
                    {
                        key: "prepareCanvas",
                        value: function () {
                            var t = this.canvas.getContext("2d");
                            t.save(),
                                (0, u.calculateEncodingAttributes)(
                                    this.encodings,
                                    this.options,
                                    t
                                );
                            var e = (0, u.getTotalWidthOfEncodings)(
                                    this.encodings
                                ),
                                n = (0, u.getMaximumHeightOfEncodings)(
                                    this.encodings
                                );
                            (this.canvas.width =
                                e +
                                this.options.marginLeft +
                                this.options.marginRight),
                                (this.canvas.height = n),
                                t.clearRect(
                                    0,
                                    0,
                                    this.canvas.width,
                                    this.canvas.height
                                ),
                                this.options.background &&
                                    ((t.fillStyle = this.options.background),
                                    t.fillRect(
                                        0,
                                        0,
                                        this.canvas.width,
                                        this.canvas.height
                                    )),
                                t.translate(this.options.marginLeft, 0);
                        },
                    },
                    {
                        key: "drawCanvasBarcode",
                        value: function (t, e) {
                            var n,
                                r = this.canvas.getContext("2d"),
                                o = e.data;
                            (n =
                                "top" == t.textPosition
                                    ? t.marginTop + t.fontSize + t.textMargin
                                    : t.marginTop),
                                (r.fillStyle = t.lineColor);
                            for (var i = 0; i < o.length; i++) {
                                var a = i * t.width + e.barcodePadding;
                                "1" === o[i]
                                    ? r.fillRect(a, n, t.width, t.height)
                                    : o[i] &&
                                      r.fillRect(
                                          a,
                                          n,
                                          t.width,
                                          t.height * o[i]
                                      );
                            }
                        },
                    },
                    {
                        key: "drawCanvasText",
                        value: function (t, e) {
                            var n,
                                r,
                                o = this.canvas.getContext("2d"),
                                i =
                                    t.fontOptions +
                                    " " +
                                    t.fontSize +
                                    "px " +
                                    t.font;
                            t.displayValue &&
                                ((r =
                                    "top" == t.textPosition
                                        ? t.marginTop +
                                          t.fontSize -
                                          t.textMargin
                                        : t.height +
                                          t.textMargin +
                                          t.marginTop +
                                          t.fontSize),
                                (o.font = i),
                                "left" == t.textAlign || e.barcodePadding > 0
                                    ? ((n = 0), (o.textAlign = "left"))
                                    : "right" == t.textAlign
                                    ? ((n = e.width - 1),
                                      (o.textAlign = "right"))
                                    : ((n = e.width / 2),
                                      (o.textAlign = "center")),
                                o.fillText(e.text, n, r));
                        },
                    },
                    {
                        key: "moveCanvasDrawing",
                        value: function (t) {
                            this.canvas.getContext("2d").translate(t.width, 0);
                        },
                    },
                    {
                        key: "restoreCanvas",
                        value: function () {
                            this.canvas.getContext("2d").restore();
                        },
                    },
                ]),
                t
            );
        })();
        e.default = f;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r,
            o = (function () {
                function t(t, e) {
                    for (var n = 0; n < e.length; n++) {
                        var r = e[n];
                        (r.enumerable = r.enumerable || !1),
                            (r.configurable = !0),
                            "value" in r && (r.writable = !0),
                            Object.defineProperty(t, r.key, r);
                    }
                }
                return function (e, n, r) {
                    return n && t(e.prototype, n), r && t(e, r), e;
                };
            })(),
            i = n(7),
            a = (r = i) && r.__esModule ? r : { default: r },
            u = n(13);
        var f = "http://www.w3.org/2000/svg",
            c = (function () {
                function t(e, n, r) {
                    !(function (t, e) {
                        if (!(t instanceof e))
                            throw new TypeError(
                                "Cannot call a class as a function"
                            );
                    })(this, t),
                        (this.svg = e),
                        (this.encodings = n),
                        (this.options = r),
                        (this.document = r.xmlDocument || document);
                }
                return (
                    o(t, [
                        {
                            key: "render",
                            value: function () {
                                var t = this.options.marginLeft;
                                this.prepareSVG();
                                for (
                                    var e = 0;
                                    e < this.encodings.length;
                                    e++
                                ) {
                                    var n = this.encodings[e],
                                        r = (0, a.default)(
                                            this.options,
                                            n.options
                                        ),
                                        o = this.createGroup(
                                            t,
                                            r.marginTop,
                                            this.svg
                                        );
                                    this.setGroupOptions(o, r),
                                        this.drawSvgBarcode(o, r, n),
                                        this.drawSVGText(o, r, n),
                                        (t += n.width);
                                }
                            },
                        },
                        {
                            key: "prepareSVG",
                            value: function () {
                                for (; this.svg.firstChild; )
                                    this.svg.removeChild(this.svg.firstChild);
                                (0, u.calculateEncodingAttributes)(
                                    this.encodings,
                                    this.options
                                );
                                var t = (0, u.getTotalWidthOfEncodings)(
                                        this.encodings
                                    ),
                                    e = (0, u.getMaximumHeightOfEncodings)(
                                        this.encodings
                                    ),
                                    n =
                                        t +
                                        this.options.marginLeft +
                                        this.options.marginRight;
                                this.setSvgAttributes(n, e),
                                    this.options.background &&
                                        this.drawRect(
                                            0,
                                            0,
                                            n,
                                            e,
                                            this.svg
                                        ).setAttribute(
                                            "style",
                                            "fill:" +
                                                this.options.background +
                                                ";"
                                        );
                            },
                        },
                        {
                            key: "drawSvgBarcode",
                            value: function (t, e, n) {
                                var r,
                                    o = n.data;
                                r =
                                    "top" == e.textPosition
                                        ? e.fontSize + e.textMargin
                                        : 0;
                                for (var i = 0, a = 0, u = 0; u < o.length; u++)
                                    (a = u * e.width + n.barcodePadding),
                                        "1" === o[u]
                                            ? i++
                                            : i > 0 &&
                                              (this.drawRect(
                                                  a - e.width * i,
                                                  r,
                                                  e.width * i,
                                                  e.height,
                                                  t
                                              ),
                                              (i = 0));
                                i > 0 &&
                                    this.drawRect(
                                        a - e.width * (i - 1),
                                        r,
                                        e.width * i,
                                        e.height,
                                        t
                                    );
                            },
                        },
                        {
                            key: "drawSVGText",
                            value: function (t, e, n) {
                                var r,
                                    o,
                                    i = this.document.createElementNS(
                                        f,
                                        "text"
                                    );
                                e.displayValue &&
                                    (i.setAttribute(
                                        "style",
                                        "font:" +
                                            e.fontOptions +
                                            " " +
                                            e.fontSize +
                                            "px " +
                                            e.font
                                    ),
                                    (o =
                                        "top" == e.textPosition
                                            ? e.fontSize - e.textMargin
                                            : e.height +
                                              e.textMargin +
                                              e.fontSize),
                                    "left" == e.textAlign ||
                                    n.barcodePadding > 0
                                        ? ((r = 0),
                                          i.setAttribute(
                                              "text-anchor",
                                              "start"
                                          ))
                                        : "right" == e.textAlign
                                        ? ((r = n.width - 1),
                                          i.setAttribute("text-anchor", "end"))
                                        : ((r = n.width / 2),
                                          i.setAttribute(
                                              "text-anchor",
                                              "middle"
                                          )),
                                    i.setAttribute("x", r),
                                    i.setAttribute("y", o),
                                    i.appendChild(
                                        this.document.createTextNode(n.text)
                                    ),
                                    t.appendChild(i));
                            },
                        },
                        {
                            key: "setSvgAttributes",
                            value: function (t, e) {
                                var n = this.svg;
                                n.setAttribute("width", t + "px"),
                                    n.setAttribute("height", e + "px"),
                                    n.setAttribute("x", "0px"),
                                    n.setAttribute("y", "0px"),
                                    n.setAttribute(
                                        "viewBox",
                                        "0 0 " + t + " " + e
                                    ),
                                    n.setAttribute("xmlns", f),
                                    n.setAttribute("version", "1.1"),
                                    n.setAttribute(
                                        "style",
                                        "transform: translate(0,0)"
                                    );
                            },
                        },
                        {
                            key: "createGroup",
                            value: function (t, e, n) {
                                var r = this.document.createElementNS(f, "g");
                                return (
                                    r.setAttribute(
                                        "transform",
                                        "translate(" + t + ", " + e + ")"
                                    ),
                                    n.appendChild(r),
                                    r
                                );
                            },
                        },
                        {
                            key: "setGroupOptions",
                            value: function (t, e) {
                                t.setAttribute(
                                    "style",
                                    "fill:" + e.lineColor + ";"
                                );
                            },
                        },
                        {
                            key: "drawRect",
                            value: function (t, e, n, r, o) {
                                var i = this.document.createElementNS(
                                    f,
                                    "rect"
                                );
                                return (
                                    i.setAttribute("x", t),
                                    i.setAttribute("y", e),
                                    i.setAttribute("width", n),
                                    i.setAttribute("height", r),
                                    o.appendChild(i),
                                    i
                                );
                            },
                        },
                    ]),
                    t
                );
            })();
        e.default = c;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
            function t(t, e) {
                for (var n = 0; n < e.length; n++) {
                    var r = e[n];
                    (r.enumerable = r.enumerable || !1),
                        (r.configurable = !0),
                        "value" in r && (r.writable = !0),
                        Object.defineProperty(t, r.key, r);
                }
            }
            return function (e, n, r) {
                return n && t(e.prototype, n), r && t(e, r), e;
            };
        })();
        var o = (function () {
            function t(e, n, r) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, t),
                    (this.object = e),
                    (this.encodings = n),
                    (this.options = r);
            }
            return (
                r(t, [
                    {
                        key: "render",
                        value: function () {
                            this.object.encodings = this.encodings;
                        },
                    },
                ]),
                t
            );
        })();
        e.default = o;
    },
    function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", { value: !0 });
        var r = (function () {
            function t(t, e) {
                for (var n = 0; n < e.length; n++) {
                    var r = e[n];
                    (r.enumerable = r.enumerable || !1),
                        (r.configurable = !0),
                        "value" in r && (r.writable = !0),
                        Object.defineProperty(t, r.key, r);
                }
            }
            return function (e, n, r) {
                return n && t(e.prototype, n), r && t(e, r), e;
            };
        })();
        var o = (function () {
            function t(e) {
                !(function (t, e) {
                    if (!(t instanceof e))
                        throw new TypeError(
                            "Cannot call a class as a function"
                        );
                })(this, t),
                    (this.api = e);
            }
            return (
                r(t, [
                    {
                        key: "handleCatch",
                        value: function (t) {
                            if ("InvalidInputException" !== t.name) throw t;
                            if (
                                this.api._options.valid ===
                                this.api._defaults.valid
                            )
                                throw t.message;
                            this.api._options.valid(!1),
                                (this.api.render = function () {});
                        },
                    },
                    {
                        key: "wrapBarcodeCall",
                        value: function (t) {
                            try {
                                var e = t.apply(void 0, arguments);
                                return this.api._options.valid(!0), e;
                            } catch (t) {
                                return this.handleCatch(t), this.api;
                            }
                        },
                    },
                ]),
                t
            );
        })();
        e.default = o;
    },
]);
