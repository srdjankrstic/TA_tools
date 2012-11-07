using System;
using System.Collections.Generic;
using LumenWorks.Framework.IO.Csv;
using System.IO;

namespace GradesourceUploader
{
	internal sealed class CsvHelper
	{
		public static IDictionary<string, IEnumerable<KeyValuePair<string, string>>> ExtractAllScoresById(string filePath)
		{
			var scoresByEmail = new Dictionary<string, IEnumerable<KeyValuePair<string, string>>>();
			
			using (var reader = new CsvReader(new StreamReader(filePath), true, ','))
			{
				reader.MissingFieldAction = MissingFieldAction.ReplaceByEmpty;
				
				while (reader.ReadNextRecord())
				{
					var scores = new List<KeyValuePair<string, string>>();
					scores.Add(new KeyValuePair<string, string>("id43", reader[12].Trim()));
					
					scoresByEmail.Add(reader[0].Trim(), scores); // index of student ID field
					
				}
			}
			return scoresByEmail;
		}
	}
}
