using System;
using LumenWorks.Framework.IO.Csv;
using System.IO;
using System.Net.Mail;
using System.Net;
using System.Threading;

namespace GradeMailer
{
	class MainClass
	{
		public static void Main (string[] args)
		{
			ReadCsv(@"/home/skrstic/temp/grades.csv", "*****");
		}
		
		static void ReadCsv(string filePath, string password)
		{
			int retries = 0;
			using (var reader = new CsvReader(new StreamReader(filePath), true, ','))
			{
				reader.MissingFieldAction = MissingFieldAction.ReplaceByEmpty;
				
				string email;
				string text;
		
				var client = new SmtpClient("smtp.gmail.com", 587);
				client.EnableSsl = true;
				client.Credentials = new NetworkCredential("srkiboy@gmail.com", password);

				while (reader.ReadNextRecord())
				{
					email = reader[3]; // field with email
					text = string.Format(@"Hi {0},

Your total score on Homework 9 is {1}. Here is the breakdown:

Problem 1: {2}
Comments: {3}

Problem 2: {4}
Comments: {5}

Peer Assessment 1: {6}
Comments: {7}

Peer Assessment 2: {8}
Comments: {9}

If you have questions about a particular grade, or a regrade request, please contact the instructor and the TAs as soon as possible

Peace,
Srdjan
", reader[1], reader[12], reader[4], reader[5], reader[6], reader[7], reader[8], reader[9], reader[10], reader[11]);
					
					var message = new MailMessage();
					message.From = new MailAddress("skrstic@cs.ucsd.edu");
					message.Subject = "CSE105 - Homework 9 score";
					message.Body = text;
					message.To.Add(new MailAddress(email));
					try
					{
						client.Send(message);
						Console.WriteLine(string.Format("Mail sent to {0}", email));
					}
					catch (Exception e)
					{
						if (retries == 3)
						{
							Console.WriteLine(string.Format(
								"Errored out too many times. The first email to not be sent out was to {0}. Error was:\n", email), e);
							throw;
						}
						Console.WriteLine("Error occured (most likely too many requests). Will wait for 30 seconds before continuing");
						retries++;
						Thread.Sleep(30000);
					}
				}
			}
		}
	}
}
