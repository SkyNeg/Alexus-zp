using System;

public partial class _Builder : System.Web.UI.Page
{
	private bool loggedIn = false;
	private string userName = "";
	private string passWord = "";
	private static string[] userNames = new string[] { "fred", "tom", "homer", "lexus" };
	private static string[] passWords = new string[] { "flintstone", "jerry", "simpson", "skripka" };

	public void Page_Load(object sender, System.EventArgs e)
	{
		if(IsPostBack)
		{
			userName = Request.Form["userName"].ToString().Trim();
			passWord = Request.Form["passWord"].ToString().Trim();
			for(int i = 0; i < userNames.Length; i++)
			{
				if(userName == userNames[i] && passWord == passWords[i])
				{
					Session["user"] = userName;
					Session["authorised"] = true;
					loggedIn = true;
					break;
				}
			}

			if(loggedIn == false)
			{
				output.Text += "You entered an incorrect username and/or password.";
			}
		}
	}
}