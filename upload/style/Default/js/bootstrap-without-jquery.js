/*!
 * Bootstrap without jQuery v0.3.1
 * By Daniel Davis under MIT License
 * https://github.com/tagawa/bootstrap-without-jquery
 * addition methods By NC22
 */

var setBootstrapEvents; // used for real-time events update

;(function() {
    'use strict';

    // querySelectorAll support for older IE
    // Source: http://weblogs.asp.net/bleroy/archive/2009/08/31/queryselectorall-on-old-ie-versions-something-that-doesn-t-work.aspx
    if (!document.querySelectorAll) {
        document.querySelectorAll = function(selector) {
            var style = document.styleSheets[0] || document.createStyleSheet();
            style.addRule(selector, "foo:bar");
            var all = document.all, resultSet = [];
            for (var i = 0, l = all.length; i < l; i++) {
                if (all[i].currentStyle.foo === "bar") {
                    resultSet[resultSet.length] = all[i];
                }
            }
            style.removeRule(0);
            return resultSet;
        };
    }

    // Get the "hidden" height of a collapsed element
    function getHiddenHeight(el) {
        var children = el.children;
        var height = 0;
        for (var i = 0, len = children.length, child; i < len; i++) {
            child = children[i];
            height += Math.max(child['clientHeight'], child['offsetHeight'], child['scrollHeight']);
        }
        return height;
    }

    // Show a dropdown menu
    function doDropdown(event) {
        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var target = evTarget.parentElement;
        var className = (' ' + target.className + ' ');
        
        if (className.indexOf(' ' + 'open' + ' ') > -1) {
            // Hide the menu
            className = className.replace(' open ', ' ');
            target.className = className;
        } else {
            // Show the menu
            target.className += ' open ';
        }
        return false;
    }
    
    function toogleAccordion(event) {

        event = event || window.event;
        var target = event.currentTarget || event.srcElement;
        var toogleId = target.href.split("#")
        var element = GetById(toogleId[1])
        
        if (element.className.indexOf('in') > -1) {
            element.className = element.className.replace('in', '');
        } else {
            element.className += ' in';
        }
        
        var accordionGroup = target.getAttribute('data-parent')
        
        if (!accordionGroup) return false;
            
        accordionGroup = accordionGroup.split("#")[1]

    	var divList   = document.getElementsByTagName('DIV')

        for (i=0; i<=divList.length-1; ++i){ 
            if (divList[i].className.indexOf('accordion-body')  > -1 && divList[i].id !== element.id) {
                divList[i].className = divList[i].className.replace('in', '');
            }
        }	    
        return false;
    }
    
   function toogleTab(event) {

        event = event || window.event;
        var target = event.currentTarget || event.srcElement;
        var toogleId = target.href.split("#")
        var tabButton = target.parentElement
        var buttons = tabButton.parentElement.getElementsByTagName('li')
        var element = GetById(toogleId[1])
        
        if (tabButton.className.indexOf('active') > -1) {
            return false; 
        }
        
        if (element.className.indexOf('active') > -1) {
            element.className = element.className.replace('active', '');
        } else {
            tabButton.className += 'active';
            element.className += ' active';
        }
        
        for (i=0; i<=buttons.length-1; ++i){ 
            if (buttons[i] != tabButton) {
                buttons[i].className = buttons[i].className.replace('active', '');
            }
        } 
        
        var tabGroup = element.parentElement
        
        if (!tabGroup) return false;

    	var divList   = document.getElementsByTagName('DIV')

        for (i=0; i<=divList.length-1; ++i){ 
            if (divList[i].className.indexOf('tab-pane')  > -1 && divList[i].id !== element.id) {
                divList[i].className = divList[i].className.replace('active', '');
            }
        }	    
        return false;
    }
    
    // Close a dropdown menu
    function closeDropdown(event) {

        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var target = evTarget.parentElement;
        
        setTimeout(function(){
            target.className = (' ' + target.className + ' ').replace(' open ', ' ')
        }, 300);

        return false;
    }

    // Close an alert box by removing it from the DOM
    function closeAlert(event) {
        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var alertBox = evTarget.parentElement;
        
        alertBox.parentElement.removeChild(alertBox);
        return false;
    }
    
    setBootstrapEvents = function() {
    
        // Set event listeners for dropdown menus
        var dropdowns = document.querySelectorAll('[data-toggle=dropdown]');
        for (var i = 0, dropdown, len = dropdowns.length; i < len; i++) {
            dropdown = dropdowns[i];
            dropdown.setAttribute('tabindex', '0'); // Fix to make onblur work in Chrome
            dropdown.onclick = doDropdown;
            dropdown.onblur = closeDropdown;
        }

        // Set event listeners for alert collapse
        var tabs = document.querySelectorAll('[data-toggle=tab]');
        for (var i = 0, len = tabs.length; i < len; i++) {
            tabs[i].onclick = toogleTab;
        }

        // Set event listeners for alert collapse
        var accords = document.querySelectorAll('[data-toggle=collapse]');
        for (var i = 0, len = accords.length; i < len; i++) {
            accords[i].onclick = toogleAccordion;
        }

        // Set event listeners for alert boxes
        var alerts = document.querySelectorAll('[data-dismiss=alert]');
        for (var i = 0, len = alerts.length; i < len; i++) {
            alerts[i].onclick = closeAlert;
        }
    }
    
    setBootstrapEvents();
})();