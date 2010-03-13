<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="_Builder"%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

	<title>Insert your own title here</title>

	<meta http-equiv="content-type" content="text/html;charset=utf-8" />

</head>

<body>

	<h1>ASP.NET Login Script</h1>

	<asp:label id="output" runat="server" />

<%
	if(Session["authorised"] == null && Session["user"] == null)
	{ %>
		<form name="login" runat="server" method="post" action="Default.aspx">
			<label for="userName">Username:</label> <input type="text" name="userName" id="userName" /><br />
			<label for="passWord">Password:</label> <input type="password" name="passWord" id="passWord" />
			<input type="submit" name="submit" value="Login" class="sub" />
		</form>
<%
	}
	else
	{ %>
		<p>You are logged in. Go view <a href="SecretData.aspx">Secret Data</a></p>
<%
	}
%>
</body>
</html>