using System;
using System.Net;
using System.IO;
using System.Text;

namespace GradesourceUploader
{
	internal static class HttpHandler
	{
		public static string HttpGet(string uri, CookieContainer cookies)
		{
   			var request = (HttpWebRequest) WebRequest.Create(uri);
			request.CookieContainer = cookies;
			request.UserAgent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20110108 Gentoo Firefox/3.6.13";
			request.Referer = uri;
//			request.KeepAlive = true;
			return GetResponse(request);
		}
		
		public static string HttpPost(string uri, string parameters, CookieContainer cookies) // parameters: name1=value1&name2=value2
		{
   			var request = (HttpWebRequest) WebRequest.Create(uri);
			request.CookieContainer = cookies;
   			request.ContentType = "application/x-www-form-urlencoded";
			request.Method = "POST";
			request.UserAgent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20110108 Gentoo Firefox/3.6.13";
			request.Accept = "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
			request.Referer = uri;
			request.KeepAlive = true;
			request.Timeout = 15000;
			var bytes = Encoding.ASCII.GetBytes(parameters);
			Stream os = null;
			
			try
			{
      			request.ContentLength = bytes.Length;
      			os = request.GetRequestStream();
      			os.Write(bytes, 0, bytes.Length);
   			}
   			catch (WebException e)
   			{
      			Console.Out.WriteLine("HttpPost: Request error. Exception:\n" + e);
		    }
   			finally
   			{
      			if (os != null)
      			{
         			os.Close();
      			}
   			}
			
			var a = GetResponse(request);
			if (a != null)
			{
				return a;
			}
			else
			{
				return HttpPost(uri, parameters, cookies);
			}
		}

		private static string GetResponse(WebRequest request)
		{
			try
			{
				using (var response = request.GetResponse())
				{
					if (response == null)
					{
						return null;
					}
					using (var reader = new StreamReader(response.GetResponseStream()))
   					{
       					return reader.ReadToEnd().Trim();
		   			}
				}
			}
			catch (WebException e)
			{
				Console.Out.WriteLine("Response error. Exception:\n" + e);
				return null;
			}
		}
		
	}
}
