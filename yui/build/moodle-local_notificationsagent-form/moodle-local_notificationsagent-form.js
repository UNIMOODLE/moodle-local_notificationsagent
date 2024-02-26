YUI.add('moodle-local_notificationsagent-form', function (Y, NAME) {

/**
 * Provides interface for users to edit availability settings on the
 * module/section editing form.
 *
 * The system works using this JavaScript plus form.js files inside each
 * condition plugin.
 *
 * The overall concept is that data is held in a textarea in the form in JSON
 * format. This JavaScript converts the textarea into a set of controls
 * generated here and by the relevant plugins.
 *
 * (Almost) all data is held directly by the state of the HTML controls, and
 * can be updated to the form field by calling the 'update' method, which
 * this code and the plugins call if any HTML control changes.
 *
 * @module moodle-local_notificationsagent-form
 */
M.local_notificationsagent = M.local_notificationsagent || {};

/**
 * @class M.local_notificationsagent
 * @extends M.core_availability
 */
M.local_notificationsagent = Y.Object(M.core_availability);

/**
 * CUSTOM ISYC
 * 
 * Called to initialise the system when the page loads. This method will
 * also call the init method for each plugin.
 *
 * @method init
 */
M.core_availability.form.init = function (pluginParams) {
    
    // Init all plugins.
    for (var plugin in pluginParams) {
        var params = pluginParams[plugin];
        var pluginClass = M[params[0]].form;
        pluginClass.init.apply(pluginClass, params);
    }

    // Get the availability field, hide it, and replace with the main div.
    this.field = Y.one('#id_availabilityconditionsjson');
    this.field.setAttribute('aria-hidden', 'true');
    // The fcontainer class here is inappropriate, but is necessary
    // because otherwise it is impossible to make Behat work correctly on
    // these controls as Behat incorrectly decides they're a moodleform
    // textarea. IMO Behat should not know about moodleforms at all and
    // should look purely at HTML elements on the page, but until it is
    // fixed to do this or fixed in some other way to only detect moodleform
    // elements that specifically match what those elements should look like,
    // then there is no good solution.
    this.mainDiv = Y.Node.create('<div class="availability-field fcontainer"></div>');
    this.field.insert(this.mainDiv, 'after');

    // Get top-level tree as JSON.
    var value = this.field.get('value');
    var data = null;
    if (value !== '') {
        try {
            data = Y.JSON.parse(value);
        } catch (x) {
            // If the JSON data is not valid, treat it as empty.
            this.field.set('value', '');
        }
    }

    var dataConditions = { c: [], op: '&', showc: true }
    var dataExceptions = { c: [], op: '!|', showc: true }
    if(data !== null){
        // Construct children.
        for (let i = 0; i < data.c.length; i++) {
            let child = data.c[i];
            if (data && data.showc !== undefined) {
                child.showc = data.showc[i];
            }
            // Conditions
            if(i==0){
                dataConditions = child
            }else if(i==1){ // Exceptions
                dataExceptions = child
            }
        }
    }

    
    // Generate root json
    this.rootList = new M.core_availability.List(null, true);

    this.rootList.removeButton();
    var newItemCondition,newItemException;
    // Create a new List object to represent the child conditions
    newItemCondition = new M.local_notificationsagent.List();
    newItemCondition.constructorChildrenCustom(dataConditions, false, this.rootList,'ac-conditions');
    // Generate json
    this.rootList.addChildCustom(newItemCondition);

    // Create a new List object to represent the child exceptions
    newItemException = new M.local_notificationsagent.List();
    newItemException.constructorChildrenCustom(dataExceptions, false, this.rootList,'ac-exceptions');
    // Generate json
    this.rootList.addChildCustom(newItemException);

    if(dataConditions.c.length > 0 || dataExceptions.c.length > 0){
        this.rootList.setVisibilityDiv(false);
    }
    
    this.mainDiv.appendChild(this.rootList.node);
    
    // Update JSON value after loading (to reflect any changes that need
    // to be made to make it valid).
    this.update();
    this.rootList.renumber();
    this.rootList.updateHtml();
    
    // // Add to select
    newItemCondition.selectAddCustom('newcondition');
    newItemException.selectAddCustom('newexception');

    // Mark main area as dynamically updated.
    this.mainDiv.setAttribute('aria-live', 'polite');

    // Listen for form submission - to avoid having our made-up fields
    // submitted, we need to disable them all before submit.
    this.field.ancestor('form').on('submit', function () {
        this.mainDiv.all('input,textarea,select').set('disabled', true);
    }, this);

    // If the form has group mode and/or grouping options, there is a
    // 'add restriction' button there.
    this.restrictByGroup = Y.one('#restrictbygroup');
    if (this.restrictByGroup) {
        this.restrictByGroup.on('click', this.addRestrictByGroup, this);
        var groupmode = Y.one('#id_groupmode');
        var groupingid = Y.one('#id_groupingid');
        if (groupmode) {
            groupmode.on('change', this.updateRestrictByGroup, this);
        }
        if (groupingid) {
            groupingid.on('change', this.updateRestrictByGroup, this);
        }
        this.updateRestrictByGroup();
    }

    const tab_active = document.querySelector('#nav-tab a.active');
    const tab_active_id = tab_active.id;
    showHideTab(tab_active_id);
}

/**
 * ISYC
 * 
 * Maintains a list of children and settings for how they are combined.
 *
 * @method constructorChildrenCustom
 * @param {Object} json Decoded JSON value
 * @param {Boolean} [false] root True if this is root level list
 * @param {Boolean} [false] root True if parent is root level list
 * @param {string} availabilityId div
 */
M.local_notificationsagent.List.prototype.constructorChildrenCustom = function(json, root, parentRoot, availabilityId) {
    // Set default value for children. (You can't do this in the prototype
    // definition, or it ends up sharing the same array between all of them.)
    this.children = [];

    if (root !== undefined) {
        this.root = root;
    }
    // Create DIV structure (without kids).
    this.node = Y.Node.create('<div id="'+availabilityId+'" class="availability-list"><h3 class="accesshide"></h3>' +
            '<div class="availability-inner hide-div">' +
            '<div class="availability-header mb-1"><span>' +
            M.util.get_string('listheader_sign_before', 'availability') + '</span>' +
            ' <label><span class="accesshide">' + M.util.get_string('label_sign', 'availability') +
            ' </span><select class="availability-neg custom-select mx-1"' +
            ' title="' + M.util.get_string('label_sign', 'availability') + '">' +
            '<option value="">' + M.util.get_string('listheader_sign_pos', 'availability') + '</option>' +
            '<option value="!">' + M.util.get_string('listheader_sign_neg', 'availability') + '</option></select></label> ' +
            '<span class="availability-single">' + M.util.get_string('listheader_single', 'availability') + '</span>' +
            '<span class="availability-multi">' + M.util.get_string('listheader_multi_before', 'availability') +
            ' <label><span class="accesshide">' + M.util.get_string('label_multi', 'availability') + ' </span>' +
            '<select class="availability-op custom-select mx-1"' +
            ' title="' + M.util.get_string('label_multi', 'availability') + '"><option value="&">' +
            M.util.get_string('listheader_multi_and', 'availability') + '</option>' +
            '<option value="|">' + M.util.get_string('listheader_multi_or', 'availability') + '</option></select></label> ' +
            M.util.get_string('listheader_multi_after', 'availability') + '</span></div>' +
            '<div class="availability-children"></div>' +
            '<div class="availability-none"><span class="px-3">' + M.util.get_string('none', 'moodle') + '</span></div>' +
            '<div class="clearfix mt-1"></div>' +
            '</div><div class="clearfix"></div></div>');
    if (!root) {
        this.node.addClass('availability-childlist d-sm-flex align-items-center');
    }
    this.inner = this.node.one('> .availability-inner');

    var shown = false;//default custom
    if (parentRoot) {
        // When the parent is root, add an eye icon before the main list div.
        if (json && json.showc !== undefined) {
            shown = json.showc;
        }
        this.eyeIcon = new M.core_availability.EyeIcon(false, shown);
        this.inner.insert(this.eyeIcon.span, 'before');
    }

    if (json) {
        // Set operator from JSON data.
        switch (json.op) {
            case '&' :
            case '|' :
                this.node.one('.availability-neg').set('value', '');
                break;
            case '!&' :
            case '!|' :
                this.node.one('.availability-neg').set('value', '!');
                break;
        }
        switch (json.op) {
            case '&' :
            case '!&' :
                this.node.one('.availability-op').set('value', '&');
                break;
            case '|' :
            case '!|' :
                this.node.one('.availability-op').set('value', '|');
                break;
        }

        // Construct children.
        for (var i = 0; i < json.c.length; i++) {
            var child = json.c[i];
            if (this.root && json && json.showc !== undefined) {
                child.showc = json.showc[i];
            }
            var newItem;
            if (child.type !== undefined) {
                // Plugin type.
                newItem = new M.core_availability.Item(child, this.root);
            } else {
                // List type.
                newItem = new M.core_availability.List(child, false, this.root);
            }
            this.addChildCustom(newItem);
        }
    }

    // Update HTML to hide unnecessary parts.
    this.updateHtml();
};

/**
 * ISYC
 * 
 * Adds a child to the end of the list (in HTML and stored data).
 *
 * @method addChildCustom
 * @private
 * @param {M.local_notificationsagent.Item|M.local_notificationsagent.List} newItem Child to add
 */
M.local_notificationsagent.List.prototype.addChildCustom = function(newItem) {
    // Add item to array and to HTML.
    this.children.push(newItem);
    this.inner.one('.availability-children').appendChild(newItem.node);
};

/**
 * ISYC
 * 
 * Remove the button in the main list.
 *
 * @method removeButton
 * @private
 * @param {M.core_availability.Item|M.core_availability.List} newItem Child to add
 */
M.core_availability.List.prototype.removeButton = function(newItem) {
    // Add item to array and to HTML.
    this.node.one('div.availability-button').remove();
};

/**
 * ISYC
 * 
 * Set setVisibilityDiv to visible or hidden
 *
 * @method setVisibilityDiv
 * @private
 * @param {Boolean} [false] root True if this is root level list
 */
M.core_availability.List.prototype.setVisibilityDiv = function(visible) {
    // Add item to array and to HTML.
    Y.one('#fitem_id_availabilityconditionsjson').setAttribute('aria-hidden', visible);
};

/**
 * ISYC
 * 
 * Add AC options to select.
 *
 * @method selectAddCustom
 * @param {String} conditionType div
 */
M.local_notificationsagent.List.prototype.selectAddCustom = function(conditionType) {
    // Add elements to select // 
    const select = Y.one('#id_'+conditionType+'_select');
    const fgroupDiv = Y.one('#fgroup_id_'+conditionType+'_group');
    const div = Y.Node.create('<div class="hide-div"></div>');

    var id, button;
    for (var type in M.core_availability.form.plugins) {
        // Plugins might decide not to display their add button.
        if (!M.core_availability.form.plugins[type].allowAdd) {
            continue;
        }

        id = conditionType + '_availability_addrestriction_' + type;

        const option = Y.Node.create('<option data-type="ac" value="'+id+'" class="availability-field fcontainer">'+M.util.get_string('title', 'availability_' + type)+'</option>');
        select.appendChild(option);

        // Add entry for plugin.
        button = Y.Node.create('<button type="button" class="btn btn-secondary w-100"' +
                'id="' + id + '">' + M.util.get_string('title', 'availability_' + type) + '</button>');
        button.on('click', this.getAddHandlerCustom(type), this);
        div.appendChild(button);
    }
    fgroupDiv.appendChild(div);
};

/**
 * ISYC
 * 
 * Gets an add handler function used by the dialogue to add a particular item.
 *
 * @method getAddHandlerCustom
 * @param {String|Null} type Type name of plugin or null to add lists
 * @return {Function} Add handler function to call when adding that thing
 */
M.local_notificationsagent.List.prototype.getAddHandlerCustom = function(type) {
    return function() {
        var newItem;
        if (type) {
            // Create an Item object to represent the child.
            newItem = new M.core_availability.Item({type: type, creating: true}, this.root);
        } else {
            // Create a new List object to represent the child.
            newItem = new M.core_availability.List({c: [], showc: true}, false, this.root);
        }
        // Add to list.
        this.addChildCustom(newItem);
        
        // Update the form and list HTML.
        M.core_availability.form.update();
        M.core_availability.form.rootList.renumber();
        this.updateHtml();
        newItem.focusAfterAdd();
        
        M.core_availability.form.rootList.setVisibilityDiv(false);//show
    };
};

/**
 * CUSTOM ISYC
 * 
 * Deletes a descendant item (Item or List). Called when the user clicks a delete icon.
 * Remove this line => this.inner.one('> .availability-button').one('button').focus();
 *
 * This is a recursive function.
 *
 * @method deleteDescendant
 * @param {M.core_availability.Item|M.core_availability.List} descendant Item to delete
 * @return {Boolean} True if it was deleted
 */
M.local_notificationsagent.List.prototype.deleteDescendant = function(descendant) {
    // Loop through children.
    for (var i = 0; i < this.children.length; i++) {
        var child = this.children[i];
        if (child === descendant) {
            // Remove from internal array.
            this.children.splice(i, 1);
            var target = child.node;

            // Remove target itself.
            this.inner.one('> .availability-children').removeChild(target);
            // Update the form and the list HTML.
            M.core_availability.form.update();
            this.updateHtml();

            if(!this.children.length>0){
                // console.log('M.local_notificationsagent.List.prototype.deleteDescendant',this.root);
                this.setVisibilityDiv(true);
            }

            return true;
        } else if (child instanceof M.core_availability.List) {
            // Recursive call.
            var found = child.deleteDescendant(descendant);
            if (found) {
                return true;
            }
        }
    }

    return false;
};

// Event to tab click
document.querySelectorAll('a[data-toggle="tab"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
        showHideTab(e.target.id);
    });
});

//////////////// UTILS //////////////////////////
function showHideTab(id){
    document.querySelector('input[name="tab-target"]').value = id;
    const ac_conditions = document.querySelector('#ac-conditions .availability-inner');
    const ac_exceptions = document.querySelector('#ac-exceptions .availability-inner');

    if(id=="nav-conditions-tab"){
        const ac_conditions_availability_children = document.querySelector('#ac-conditions .availability-inner > .availability-children');
        const check = !ac_conditions_availability_children.children.length > 0;
        document.getElementById("fitem_id_availabilityconditionsjson").setAttribute('aria-hidden', check);
        ac_conditions.classList.remove("hide-div");
        ac_exceptions.classList.add("hide-div");
    }else if(id=="nav-exceptions-tab"){
        const ac_exceptions_availability_children = document.querySelector('#ac-exceptions .availability-inner > .availability-children');
        const check = !ac_exceptions_availability_children.children.length > 0;
        document.getElementById("fitem_id_availabilityconditionsjson").setAttribute('aria-hidden', check);
        ac_exceptions.classList.remove("hide-div");
        ac_conditions.classList.add("hide-div");
    }else{
        ac_conditions.classList.add("hide-div");
        ac_exceptions.classList.add("hide-div");
        document.getElementById("fitem_id_availabilityconditionsjson").setAttribute('aria-hidden', true);
    }
}

}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
