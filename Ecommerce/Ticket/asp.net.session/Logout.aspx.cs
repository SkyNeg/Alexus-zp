using System;

public partial class _Builder : System.Web.UI.Page
{
	public void Page_Load(object sender, System.EventArgs e)
	{
		if(Session["authorised"] != null && Session["user"] != null)
		{
			Session.Remove("authorised");
			Session.Remove("user");
			output.Text = "<p>You are now logged out.</p>";
		}
		else
		{
			output.Text = "<p>You cannot logout as you are not logged in. <a href=\"Default.aspx\" title=\"Login\">Login</a></p>";
		}
	}
}