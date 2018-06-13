function Usergroup(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['label', 'capabilities'];


	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.allowedFields = function () {
		return _self.allowedFields.slice();
	}; // allowedFields

	this.replace = function (data) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-users') ) {
			return false;
		}

		// Removed fields that aren't allowed.
		for (var field in data ) {
			if ( self.allowedFields().indexOf(field) == -1 ) {
				delete data[field];
			}
		}

		// Update the record.
		$.extend(_self.record, data);

		var ajaxDate = {
			type: 'usergroup',
			action: 'replace',
			usergroup_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		});

	} // replace

} // Usergroup


module.exports = Usergroup