// OWServer Plugin for Vera
// For: Embedded Data Systems
// (c) Chris Jackson

var Devices = [];
var DeviceCaps = [];
var TypeTable  = [];


// Register the devices...
function OWpluginRegister()
{
	// Loop through the devices
	var numDevices = 0;

	//Changed from using a hash to using an object to define the parameters
	var RequestParms = new Object();

	for(iDevice in Devices) {
		numDevices++;
		var elSelect = document.getElementById('OWVar-'+iDevice);
		Devices[iDevice].Type = elSelect.value;
		
		RequestParms["Rom"+numDevices] = Devices[iDevice].ROMId;
		RequestParms["Dev"+numDevices] = Devices[iDevice].Device;
		RequestParms["Typ"+numDevices] = Devices[iDevice].Type;
	}

	if(numDevices == 0)
		return;

	RequestParms["id"] = "lr_owCtrl";
	RequestParms["funct"] = "create";
	RequestParms["cnt"] = numDevices;

	new Ajax.Request("/port_3480/data_request", {
		method: "get",
		parameters: RequestParms,
		onSuccess: function(transport) {
			set_panel_html("Configuration has been sent to Vera. LuaPnP will restart and the new devices should take effect...");
		},
		onFailure: function() {
		}
	});
}

function showDevices(deviceId)
{
	// Get the TypeTable
	new Ajax.Request("/port_3480/data_request", {
		method: "get",
		parameters: {
			id: "lr_owCtrl",
			funct: "gettypes",
			format: "json"
		},
		onSuccess: function(transport) {
			TypeTable = transport.responseText.evalJSON();

			// Get the TypeTable
			new Ajax.Request("/port_3480/data_request", {
				method: "get",
				parameters: {
					id: "lr_owCtrl",
					funct: "getdevcap",
					format: "json"
				},
				onSuccess: function(transport) {
					DeviceCaps = transport.responseText.evalJSON();

					// Populate the configuration list
					new Ajax.Request("/port_3480/data_request", {
						method: "get",
						parameters: {
							id: "lr_owCtrl",
							funct: "getnew",
							format: "json"
						},
						onSuccess: function(transport) {
							Devices = transport.responseText.evalJSON();

							var DeviceCnt = 0;
							for(iDevice in Devices)
								DeviceCnt++;

							var innerHTML = "";
							var lastId    = "";
							var style     = "";

							if(Devices.length == 0) {
								innerHTML = "No new devices to add";
							}
							else {
								innerHTML = "<div style='width:570px;' id='OWServer-device-list'>" +
											"<div style=\"border-bottom:2px solid red;margin:6px;\">" +
											"<table><tr>"+
											"<td width='160px'>Total NEW devices: <b>" + DeviceCnt + "</b></td>" +
											"<td><input type='button' onclick='OWpluginRegister();' value='Add' class='button'></td>" +
											"<td width='30px'>&nbsp;</td>" +
											"<td>Check <b>ALL</b> entries before clicking the Add button.</td>" +
											"</tr></table></div>";
								// Loop through the devices
								for(var iDevice=0; iDevice < DeviceCnt; iDevice++) {
									if(Devices[iDevice].ROMId != lastId) {
										style = "border-top:3px solid green;margin:2px;"
										lastId = Devices[iDevice].ROMId;
									}
									else
										style = "border-top:1px solid blue;margin:2px;"

									// New Device header
									innerHTML += "<div style='" + style + "'>" +
														  "<table><tr>"+
														  "<td width='115'>Device: " + DeviceCaps[Devices[iDevice].Device].Device  + "</td>" +
														  "<td width='140'>" + Devices[iDevice].ROMId + "</td>" +
														  "<td width='130'>" + DeviceCaps[Devices[iDevice].Device].Name + "</td>" +
														  "<td><form>" +
														  "<select id='OWVar-" + iDevice + "' style='width:185px'>";

									var numOptions = DeviceCaps[Devices[iDevice].Device].Services.length;
									for(var iOpt=0; iOpt<numOptions; iOpt++) {
										innerHTML += "<option value='" + DeviceCaps[Devices[iDevice].Device].Services[iOpt] + "'>" + TypeTable[DeviceCaps[Devices[iDevice].Device].Services[iOpt]].Name + "</option>";
									}

									innerHTML += "</select>" +
												  "</form></td>" +
												  "</tr></table></div>";
								}
								innerHTML += "</div>";
							}
							set_panel_html(innerHTML);
						},
						onFailure: function() {
						}
					});
				},
				onFailure: function() {
				}
			});
		},
		onFailure: function() {
		}
	});

 	return true;
}

