// ========================================================================
//        Copyright © 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

//	Convert an INPUT control into one that lists the available plugins and allows choosing one.
//	Usage:
//
//	<INPUT id="my_input_element" name="Whatever" value="2">
//	<SCRIPT type="text/javascript">new PluginSelect(Utilities_GetElementById('my_input_element'), '64.1.files', 1, 'Personal');</SCRIPT>

function PluginSelect(targetInput, filesLocation, rootLocation, initialLabel)
{
	// Make sure it's the right type of element
	if (targetInput.tagName == 'INPUT')
	{
		this.filesLocation = filesLocation;
		this.rootLocation = rootLocation;
		
		// Where to set the items
		this.targetInput = targetInput;

		// Create the actual element that will be shown
		this.newDivElement = document.createElement('DIV');
		this.targetInput.parentNode.insertBefore(this.newDivElement, this.targetInput);

		// Hide the target element: we can't use display:none because Safari doesn't like it
		this.targetInput.style.position = 'absolute';
		this.targetInput.style.left = '-1000px';
		
		createDropDownButton(this, initialLabel);
	}
	else
	{
		alert('Error: not an INPUT element');
	};
};

function createDropDownButton(pluginSelect, label)
{
	if (pluginSelect.dropDownButton)
	{
		pluginSelect.dropDownButton.destroy();
	}
	
	pluginSelect.dropDownButton = new dijit.form.DropDownButton({label: ""}, pluginSelect.newDivElement);

	// Reference
	pluginSelect.dropDownButton.pluginSelect = pluginSelect;

	var subMenu = new dijit.Menu({fileLocation: pluginSelect.rootLocation, isLoaded: false, pluginSelect: pluginSelect});
	dojo.connect(subMenu, 'onOpen', menuOpened);
	pluginSelect.dropDownButton.dropDown = subMenu;
	pluginSelect.dropDownButton.startup();
	
	setLabel(pluginSelect, label);
}

function setLabel(pluginSelect, label)
{
	// DRL FIXIT! There was an exception thrown when I tried to set the label on the drop down button so I had to
	// create a seperate control just to show the label!
	
	if (pluginSelect.labelControl)
		pluginSelect.targetInput.parentNode.removeChild(pluginSelect.labelControl);
	
	pluginSelect.labelControl = document.createElement('font');
	pluginSelect.labelControl.innerHTML = label;
	pluginSelect.targetInput.parentNode.insertBefore(pluginSelect.labelControl, pluginSelect.targetInput.parentNode.firstChild);
}

function menuOpened()
{
	loadMenu(this);
}

function itemClicked()
{
	setLabel(this.pluginSelect, this.label);
	
	this.pluginSelect.targetInput.value = this.fileLocation;
}

function processDataRow(row)
{
	var item = new dijit.MenuItem({label: row.Name, fileLocation: row.Location, pluginSelect: targetMenu.pluginSelect});
	dojo.connect(item, 'onClick', itemClicked);
	targetMenu.addChild(item);
	
	if (row.Type == 'folder')
	{
		var subMenu = new dijit.Menu({fileLocation: row.Location, isLoaded: false, pluginSelect: targetMenu.pluginSelect});
		dojo.connect(subMenu, 'onOpen', menuOpened);
		var item = new dijit.PopupMenuItem({label: row.Name + " >", popup: subMenu, fileLocation: row.Location, pluginSelect: targetMenu.pluginSelect});
		dojo.connect(item, 'onClick', itemClicked);
		targetMenu.addChild(item);
	}
}

function dataLoaded(response, ioArgs)
{
//	alert("Result: " + response);
	dojo.forEach(response.ResultSet.Result, processDataRow);
	targetMenu.isLoaded = true;
	return response;
}

function dataError(response)
{
	dojo.byId('myResponse').innerHTML = response;
	return response;
}

var targetMenu;		// FIXIT! Get rid of this global variable!
function loadMenu(menu)
{
	if (menu.isLoaded)
		return;

	targetMenu = menu;

	var parent;	
	if (menu.fileLocation != null)
	{
		parent = 'ParentLocation%3D%22' + menu.fileLocation + '%22';
	}
	else
	{
		parent = 'ISNULL(ParentLocation)';
	}

	var kw =
	{
		// DRL FIXIT! The location below should be encoded!
		url: '/cgi-bin/Ajax/Ajax.cgi?Query=SELECT%20Location%2CName%2CType%20FROM%20' +
			menu.pluginSelect.filesLocation + '%20WHERE%20' + parent,
		headers: { "Accept": "application/json" },
		handleAs: "json",
		timeout: 20000,
		load: dataLoaded,
		error: dataError
	};

	dojo.xhrGet(kw);
}
