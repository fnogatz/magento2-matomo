/**
 * Copyright 2016-2018 Henrik Hedelund
 * Copyright 2020      Falco Nogatz
 *
 * This file is part of Chessio_Matomo.
 *
 * Chessio_Matomo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Chessio_Matomo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Chessio_Matomo.  If not, see <http://www.gnu.org/licenses/>.
 */

define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, _, customerData) {
    'use strict';

    /**
     * Object holding globally accessible properties
     *
     * @type {Object}
     */
    var exports = window;

    /**
     * Default Matomo website ID
     *
     * @type {number}
     */
    var defaultSiteId;

    /**
     * Default Matomo tracker endpoint
     *
     * @type {String}
     */
    var defaultTrackerUrl;

    /**
     * Use Tag Manager container instead of direct tracker?
     *
     * @type {bool}
     */
    var isContainerEnabled;

    /**
     * Reference to global `matomoAsyncInit' in case we overwrite something
     *
     * @type {Function|undefined}
     */
    var origMatomoAsyncInit = exports.matomoAsyncInit;

    /**
     * Matomo singleton/namespace
     *
     * @type {Object}
     */
    var matomo = exports.Matomo || null;

    /**
     * Collection of matomo promises
     *
     * @type {Array.<Deferred>}
     */
    var matomoPromises = [];

    /**
     * Client side cache/storage
     *
     * @type {Object}
     */
    var storage = $.initNamespaceStorage('chessio-matomo').localStorage;

    /**
     * Cart data access
     *
     * @type {Object}
     */
    var cartObservable = customerData.get('cart');

    /**
     * Customer data access
     *
     * @type {Object}
     */
    var customerObservable = customerData.get('customer');

    /**
     * Append Matomo tracker script URL to head
     *
     * @param {String} scriptUrl
     */
    function injectScript(scriptUrl) {
        $('<script>')
            .attr('type', 'text/javascript')
            .attr('async', true)
            .attr('defer', true)
            .attr('src', scriptUrl)
            .appendTo('head');
    }

    /**
     * Resolve (or reject) requests for the Matomo singleton
     */
    function resolveMatomoPromises()
    {
        if (matomo) {
            _.each(matomoPromises, function (deferred) {
                deferred.resolve(matomo);
            });
        } else {
            _.each(matomoPromises, function (deferred) {
                deferred.reject();
            });
        }
    }

    /**
     * Callback for when the injected Matomo script is ready
     */
    function onMatomoLoaded() {
        if (_.isFunction(origMatomoAsyncInit)) {
            origMatomoAsyncInit();
        }
        matomo = _.isObject(exports.Matomo) ? exports.Matomo : false;
        if (defaultSiteId && defaultTrackerUrl) {
            resolveMatomoPromises();
        }
    }

    /**
     * Get Matomo singleton/namespace promise
     *
     * @returns {Promise}
     */
    function getMatomoPromise() {
        var deferred = $.Deferred();

        if (matomo === null || !defaultSiteId || !defaultTrackerUrl) {
            matomoPromises.push(deferred);
        } else if (matomo === false) {
            deferred.reject();
        } else {
            deferred.resolve(matomo);
        }
        return deferred.promise();
    }

    /**
     * Get asynchronous Matomo tracker promise
     *
     * @returns {Promise}
     */
    function getAsyncTrackerPromise()
    {
        var deferred = $.Deferred();

        getMatomoPromise()
            .done(function (matomoObject) {
                deferred.resolve(matomoObject.getAsyncTracker());
            })
            .fail(function () {
                deferred.reject();
            });
        return deferred.promise();
    }

    /**
     * Create a new Matomo tracker promise
     *
     * @param {String|undefined} trackerUrl
     * @param {number|undefined} siteId
     * @returns {Promise}
     */
    function getTrackerPromise(trackerUrl, siteId) {
        var deferred = $.Deferred();

        getMatomoPromise()
            .done(function (matomoObject) {
                deferred.resolve(matomoObject.getTracker(
                    trackerUrl || defaultTrackerUrl,
                    siteId || defaultSiteId
                ));
            })
            .fail(function () {
                deferred.reject();
            });
        return deferred.promise();
    }

    /**
     * Push an action to the given tracker. If the tracker argument is
     * omitted the action will be picked up by the async tracker.
     *
     * @param {Array} action
     * @param {Tracker|undefined} tracker
     */
    function pushAction(action, tracker) {

        var event, actionName;

        if (!_.isArray(action) || _.isEmpty(action)) {
            return;
        } else if (_.isArray(_.first(action))) {
            _.each(action, function (subAction) {
                pushAction(subAction, tracker);
            });
            return;
        }

        if (/^track/.test(_.first(action))) {
            // Trigger event before tracking
            event = $.Event('matomo:beforeTrack');
            $(exports).triggerHandler(event, [action, tracker]);
            if (event.isDefaultPrevented()) {
                // Skip tracking if event listener prevented default
                return;
            } else if (_.isArray(event.result)) {
                // Replace track action if event listener returned an array
                action = event.result;
            }
        }

        if (_.isObject(tracker)) {
            actionName = action.shift();
            if (_.isFunction(tracker[actionName])) {
                tracker[actionName].apply(tracker, action);
            }
        } else {
            if (isContainerEnabled) {
                actionName = action.shift();
                var data = {
                    'event': actionName,
                }
                data['parameters'] = action;
                exports._mtm.push(data);
            } else {
                exports._paq.push(action);
            }
        }
    }

    /**
     * Callback for cart customer data subscriber
     *
     * @param {Object} cart
     * @see \Chessio\Matomo\CustomerData\Checkout\CartPlugin
     */
    function cartUpdated(cart) {

        // Check in storage if we have registered this cart already
        if (_.has(cart, 'data_id')) {
            if (storage.get('cart-data-id') === cart.data_id) {
                return;
            }
            storage.set('cart-data-id', cart.data_id);
        }

        if (_.has(cart, 'matomoActions')) {
            // We need to create a new tracker instance for asynchronous
            // ecommerce updates since previous ecommerce items are stored
            // in the tracker.
            getTrackerPromise().done(function (tracker) {
                pushAction(cart.matomoActions, tracker);
            });
        }
    }

    /**
     * Callback for customer data subscriber
     *
     * @param {Object} customer
     * @see \Chessio\Matomo\CustomerData\Customer\CustomerPlugin
     */
    function customerUpdated(customer) {
        if (_.has(customer, 'matomoUserId')) {
            storage.set('user-id', customer.matomoUserId);
        } else {
            storage.remove('user-id');
        }
    }

    /**
     * Event listener for `matomo:beforeTrack'. Adds visitor data to tracker.
     *
     * @param {jQuery.Event} event
     * @param {Array} action
     * @param {Tracker|undefined} tracker
     */
    function addVisitorDataBeforeTrack(event, action, tracker) {
        if (storage.isSet('user-id')) {
            pushAction(['setUserId', storage.get('user-id')], tracker);
        }
    }

    /**
     * Checks that matomo.js is already on page
     *
     * @param {String} scriptUrl
     * @returns {boolean}
     */
    function scriptExists(scriptUrl) {
        return $('script[src="' + scriptUrl + '"]').length === 1;
    }

    /**
     * Initialize this component with given options
     *
     * @param {Object} options
     */
    function initialize(options) {
        isContainerEnabled = options.isContainerEnabled;

        if (!isContainerEnabled) {
            defaultSiteId = options.siteId;
            defaultTrackerUrl = options.trackerUrl;

            if (matomo === null) {
                if (!scriptExists(options.scriptUrl)) {
                    pushAction([
                        ['setSiteId', defaultSiteId],
                        ['setTrackerUrl', defaultTrackerUrl]
                    ]);
                    injectScript(options.scriptUrl);
                }
            } else {
                // If we already have the Matomo object we can resolve any pending
                // promises immediately.
                resolveMatomoPromises();
            }
        }
        pushAction(options.actions);
    }

    // Make sure the Matomo asynchronous tracker queue is defined
    if (isContainerEnabled) {
        exports._mtm = exports._mtm || [];
    } else {
        exports._paq = exports._paq || [];
    }

    // Listen for when the Matomo asynchronous tracker is ready
    exports.matomoAsyncInit = onMatomoLoaded;
    // Subscribe to cart updates
    cartObservable.subscribe(cartUpdated);
    // Subscribe to customer updates
    customerObservable.subscribe(customerUpdated);
    // Listen for track actions to inject visitor data
    $(exports).on('matomo:beforeTrack', addVisitorDataBeforeTrack);

    return {
        // Public component API
        createTracker: getTrackerPromise,
        getMatomo: getMatomoPromise,
        getTracker: getAsyncTrackerPromise,
        push: pushAction,
        // Entrypoint called with options from matomo.phtml
        // @see /lib/web/mage/apply/main.js:init
        'Chessio_Matomo/js/tracker': initialize
    };
});
