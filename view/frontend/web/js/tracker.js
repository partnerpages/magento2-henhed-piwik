/**
 * Copyright 2016 Henrik Hedelund
 *
 * This file is part of Partnerpages_Piwik.
 *
 * Partnerpages_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Partnerpages_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Partnerpages_Piwik.  If not, see <http://www.gnu.org/licenses/>.
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
     * Default Piwik website ID
     *
     * @type {number}
     */
    var defaultSiteId;

    /**
     * Default Piwik tracker endpoint
     *
     * @type {String}
     */
    var defaultTrackerUrl;

    /**
     * Reference to global `piwikAsyncInit' in case we overwrite something
     *
     * @type {Function|undefined}
     */
    var origPiwikAsyncInit = exports.piwikAsyncInit;

    /**
     * Piwik singleton/namespace
     *
     * @type {Object}
     */
    var piwik = exports.Piwik || null;

    /**
     * Collection of piwik promises
     *
     * @type {Array.<Deferred>}
     */
    var piwikPromises = [];

    /**
     * Client side cache/storage
     *
     * @type {Object}
     */
    var storage = $.initNamespaceStorage('partnerpages-piwik').localStorage;

    /**
     * Append Piwik tracker script URL to head
     *
     * @param {String} scriptUrl
     * @return bool Wheter the script was injected
     */
    function injectScript(scriptUrl) {
        if (piwik === null) {
            $('<script>')
                .attr('type', 'text/javascript')
                .attr('async', true)
                .attr('defer', true)
                .attr('src', scriptUrl)
                .appendTo('head');
            return true;
        }
        return false; // Script is already included
    }

    /**
     * Resolve (or reject) requests for the Piwik singleton
     */
    function resolvePiwikPromises()
    {
        if (piwik) {
            _.each(piwikPromises, function (deferred) {
                deferred.resolve(piwik);
            });
        } else {
            _.each(piwikPromises, function (deferred) {
                deferred.reject();
            });
        }
    }

    /**
     * Callback for when the injected Piwik script is ready
     */
    function onPiwikLoaded() {
        if (_.isFunction(origPiwikAsyncInit)) {
            origPiwikAsyncInit();
        }
        piwik = _.isObject(exports.Piwik) ? exports.Piwik : false;
        if (defaultSiteId && defaultTrackerUrl) {
            resolvePiwikPromises();
        }
    }

    /**
     * Get Piwik singleton/namespace promise
     *
     * @returns {Promise}
     */
    function getPiwikPromise() {
        var deferred = $.Deferred();
        if (piwik === null || !defaultSiteId || !defaultTrackerUrl) {
            piwikPromises.push(deferred);
        } else if (piwik === false) {
            deferred.reject();
        } else {
            deferred.resolve(piwik);
        }
        return deferred.promise();
    }

    /**
     * Get asynchronous Piwik tracker promise
     *
     * @returns {Promise}
     */
    function getAsyncTrackerPromise()
    {
        var deferred = $.Deferred();
        getPiwikPromise()
            .done(function (piwik) {
                deferred.resolve(piwik.getAsyncTracker());
            })
            .fail(function () {
                deferred.reject();
            });
        return deferred.promise();
    }

    /**
     * Create a new Piwik tracker promise
     *
     * @param {String|undefined} trackerUrl
     * @param {number|undefined} siteId
     * @returns {Promise}
     */
    function getTrackerPromise(trackerUrl, siteId) {
        var deferred = $.Deferred();
        getPiwikPromise()
            .done(function (piwik) {
                deferred.resolve(piwik.getTracker(
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
        if (!_.isArray(action) || _.isEmpty(action)) {
            return;
        } else if (_.isArray(_.first(action))) {
            _.each(action, function (subAction) {
                pushAction(subAction, tracker);
            });
        } else if (_.isObject(tracker)) {
            var actionName = action.shift();
            if (_.isFunction(tracker[actionName])) {
                tracker[actionName].apply(tracker, action);
            }
        } else {
            exports._paq.push(action);
        }
    }

    /**
     * Callback for cart customer data subscriber
     *
     * @param {Object} cart
     * @see \Partnerpages\Piwik\CustomerData\Checkout\CartPlugin
     */
    function cartUpdated(cart) {

        // Check in storage if we have registered this cart already
        if (_.has(cart, 'data_id')) {
            if (storage.get('cart-data-id') === cart.data_id) {
                return;
            } else {
                storage.set('cart-data-id', cart.data_id);
            }
        }

        if (_.has(cart, 'piwikActions')) {
            // We need to create a new tracker instance for asynchronous
            // ecommerce updates since previous ecommerce items are stored
            // in the tracker.
            getTrackerPromise().done(function (tracker) {
                pushAction(cart.piwikActions, tracker);
            });
        }
    }

    /**
     * Initialzie this component with given options
     *
     * @param {Object} options
     */
    function initialize(options) {
        pushAction([
            ['setSiteId', defaultSiteId = options.siteId],
            ['setTrackerUrl', defaultTrackerUrl = options.trackerUrl]
        ]);
        pushAction(options.actions);
        if (!injectScript(options.scriptUrl)) {
            // If the tracker script was not injected we already have the Piwik
            // object and can resolve promises immediately.
            resolvePiwikPromises();
        }
    }

    // Make sure the Piwik asynchronous tracker queue is defined
    exports._paq = exports._paq || [];
    // Listen for when the Piwik asynchronous tracker is ready
    exports.piwikAsyncInit = onPiwikLoaded;
    // Subscribe to cart updates
    customerData.get('cart').subscribe(cartUpdated);

    return {
        // Public component API
        createTracker: getTrackerPromise,
        getPiwik: getPiwikPromise,
        getTracker: getAsyncTrackerPromise,
        push: pushAction,
        // Entrypoint called with options from piwik.phtml
        // @see /lib/web/mage/apply/main.js:init
        'Partnerpages_Piwik/js/tracker': initialize
    };
});
