using System;

public partial class _Builder : System.Web.UI.Page
{
	public void Page_Load(object sender, System.EventArgs e)
	{
		if(Session["authorised"] != null && Session["user"] != null)
		{
			output.Text = "<p>Miss Scarlet did it in the Study with a Candle stick.</p>";
		}
		else
		{
			output.Text = "<p>You are not logged in. Unlucky</p>";
		}
	}
}