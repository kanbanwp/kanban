function User (user)
{
	this.record = function()
	{
		return user;
	}
}



User.prototype.has_cap = function(cap)
{
	try
	{
		return this.record().caps.indexOf(cap) < 0 ? false : true;
	}
	catch (err)
	{
		return false;
	}
}
