using System;
using System.Linq;
using System.Collections.Generic;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;

namespace GradesourceUploader
{
	internal sealed class GradesourceSession : IDisposable
	{
		private static readonly String _REGEX = "<input type=\"text\" name='(.*)' value=\"(.*)\" size=\"8\"><input type=\"hidden\" value=\"(.*)\" name=\'(.*)\'></td>";
		private static IDictionary<string, int> _gradesourceIds;
		private static CookieContainer _cookies;
		
		public GradesourceSession(IDictionary<string, int> gradesourceIds, string password)
		{
			_gradesourceIds = gradesourceIds;
			_cookies = new CookieContainer();

			HttpHandler.HttpPost("https://www.gradesource.com/validate.asp", string.Format("User=srdjan&Password={0}", password), _cookies);
		}
		
		public void PostStudentScore(string email, string field, string score)
		{
			if (!_gradesourceIds.ContainsKey(email))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: student with PID {0} is in the spreadsheet but I don't know their GradeSource ID", email));
				return;
			}
			
			var gsId = _gradesourceIds[email];
			var editPage = HttpHandler.HttpGet(string.Format("https://www.gradesource.com/editscores2.asp?id={0}", gsId), _cookies);
			
			// Mega-hack
			if (!editPage.Contains("Edit Scores By Student"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: couldn't reach the edit page for student with pid {0} and GradeSource ID {1}", email, gsId));
				return;
			}
			
			var response = HttpHandler.HttpPost(string.Format("https://www.gradesource.com/updatescores2.asp?id={0}", gsId),
			                     CreatePostParameters(editPage, gsId, field, score),
			                     _cookies);
			
			// keep it classy
			if (response.Contains("Please verify that the scores are correct before continuing"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: didn't post the score for student with pid {0} and GradeSource ID {1}, because the score ({2}) was invalid or out of range",
				    email, gsId, score));
			}
			else if (!response.Contains("<title>GradeSource - Scores</title>"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: didn't post the score for student with pid {0} and GradeSource ID {1}, for unknown reasons",
					email, gsId));
			}
		}
		
		public void PostStudentScores(string email, IEnumerable<KeyValuePair<string, string>> scoresByField)
		{
			if (!_gradesourceIds.ContainsKey(email))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: student with pid {0} is in the spreadsheet but I don't know their GradeSource ID", email));
				return;
			}
			
			var gsId = _gradesourceIds[email];
			var editPage = HttpHandler.HttpGet(string.Format("https://www.gradesource.com/editscores2.asp?id={0}", gsId), _cookies);
			
			// Mega-hack
			if (!editPage.Contains("Edit Scores By Student"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: couldn't reach the edit page for student with pid {0} and GradeSource ID {1}", email, gsId));
				return;
			}
			
			var response = HttpHandler.HttpPost(string.Format("https://www.gradesource.com/updatescores2.asp?id={0}", gsId),
			                     CreatePostParameters(editPage, gsId, scoresByField),
			                     _cookies);
			
			// keep it classy
			if (response.Contains("Please verify that the scores are correct before continuing"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: didn't post the score for student with pid {0} and GradeSource ID {1}, because some score was invalid or out of range",
				    email, gsId));
			}
			else if (!response.Contains("<title>GradeSource - Scores</title>"))
			{
				Console.Out.WriteLine(string.Format(
					"WARNING: didn't post the score for student with pid {0} and GradeSource ID {1}, for unknown reasons",
					email, gsId));
			}
		}
		
		// this whole thing is utter hacky bullcrap
		private static string CreatePostParameters(string editPage, int studentId, string field, string score)
		{
			var postParams = new StringBuilder("verifyAccepted=N");
			
			var matches = Regex.Matches(editPage, _REGEX);
			foreach (Match match in matches)
			{
				var groups = match.Groups;
				var scoreName = groups[1].Value;
				var scoreValue = groups[2].Value;
				var idValue = groups[3].Value;
				var idName = groups[4].Value;
				if (idName == field)
				{
					scoreValue = score;
				}
				postParams.Append(string.Format("&{0}={1}&{2}={3}", scoreName, scoreValue, idName, idValue));
			}
			
			postParams.Append(string.Format("&studentId={0}&assessmentCount={1}", studentId, matches.Count));
			
			return postParams.ToString();
		}
		
		private static string CreatePostParameters(string editPage, int studentId, IEnumerable<KeyValuePair<string, string>> scoresByField)
		{
			var postParams = new StringBuilder("verifyAccepted=N");
			
			var matches = Regex.Matches(editPage, _REGEX);
			foreach (Match match in matches)
			{
				var groups = match.Groups;
				var scoreName = groups[1].Value;
				var scoreValue = groups[2].Value;
				var idValue = groups[3].Value;
				var idName = groups[4].Value;
				if (scoresByField.Count(kp => kp.Key == idName) > 0)
				{
					scoreValue = scoresByField.Where(kp => kp.Key == idName).First().Value;
				}
				postParams.Append(string.Format("&{0}={1}&{2}={3}", scoreName, scoreValue, idName, idValue));
			}
			
			postParams.Append(string.Format("&studentId={0}&assessmentCount={1}", studentId, matches.Count));
			
			return postParams.ToString();
		}
		
		// meh, dealing with the http login was a lot less of a pain in the ass so I don't really need
		// the dispose pattern, but I like using "using" so let it be
		public void Dispose()
    	{
	        Dispose(true);
        	GC.SuppressFinalize(this);
    	}

    	private void Dispose(bool disposing)
	    {
    	    if (disposing) // managed
        	{
            	_gradesourceIds = null;
				_cookies = null;
        	}   
        	// unmanaged
    	}

    	~GradesourceSession()
    	{
	        Dispose(false);
    	}
	}
}
