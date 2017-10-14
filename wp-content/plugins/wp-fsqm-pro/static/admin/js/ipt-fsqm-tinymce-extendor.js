(function() {
	"use strict";

	window.iptFSQMTMMenu = {
		forms: [],
		themes: [],
		sloads: [],
		rtype: [],
		rdata: [],
		rappe: [],
		addons: []
	};

	var i, checked;
	// Prepopulate the forms for easier entry
	if ( typeof ( iptFSQMTML10n.forms ) == 'object' && iptFSQMTML10n.forms.length > 0 ) {
		for ( i in iptFSQMTML10n.forms ) {
			iptFSQMTMMenu.forms[iptFSQMTMMenu.forms.length] = {
				text: iptFSQMTML10n.forms[i].name,
				value: iptFSQMTML10n.forms[i].id
			};
		}
	}

	// Prepopulate the themes for easier entry
	if ( typeof ( iptFSQMTML10n.themes ) == 'object' ) {
		for ( i in iptFSQMTML10n.themes ) {
			iptFSQMTMMenu.themes[iptFSQMTMMenu.themes.length] = {
				text: iptFSQMTML10n.themes[i],
				value: i
			};
		}
	}

	// Prepopulate the trends variables
	if ( typeof ( iptFSQMTML10n.trends ) == 'object' ) {
		// Report type
		for ( i in iptFSQMTML10n.trends.reportTypes ) {
			iptFSQMTMMenu.rtype[ iptFSQMTMMenu.rtype.length ] = {
				type: 'checkbox',
				name: 'fsqmTW1rt' + iptFSQMTML10n.trends.reportTypes[ i ].value,
				text: iptFSQMTML10n.trends.reportTypes[ i ].text,
				// value: iptFSQMTML10n.trends.reportTypes[ i ].value,
				checked: iptFSQMTML10n.trends.reportTypes[ i ].checked
			};
		}
		// Report Data
		for ( i in iptFSQMTML10n.trends.reportData ) {
			iptFSQMTMMenu.rdata[ iptFSQMTMMenu.rdata.length ] = {
				type: 'checkbox',
				name: 'fsqmTW1rd' + iptFSQMTML10n.trends.reportData[ i ].value,
				text: iptFSQMTML10n.trends.reportData[ i ].text,
				// value: iptFSQMTML10n.trends.reportData[ i ].value,
				checked: iptFSQMTML10n.trends.reportData[ i ].checked
			};
		}
		// Report appearance
		for ( i in iptFSQMTML10n.trends.reportAppearance ) {
			iptFSQMTMMenu.rappe[ iptFSQMTMMenu.rappe.length ] = {
				type: 'checkbox',
				name: 'fsqmTW1ra' + iptFSQMTML10n.trends.reportAppearance[ i ].value,
				text: iptFSQMTML10n.trends.reportAppearance[ i ].text,
				// value: iptFSQMTML10n.trends.reportAppearance[ i ].value,
				checked: iptFSQMTML10n.trends.reportAppearance[ i ].checked
			};
		}
	}
})();
