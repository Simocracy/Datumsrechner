using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Simocracy
{
	/// <summary>
	/// Datumsrechner für Simocracy
	/// </summary>
	/// <remarks>
	/// Optimiert und Übersetzt vom Simocracy PostWriter 2.0.5
	/// Basierend auf dem alten PHP-Datumsrechner von Fluggi
	/// </remarks>
	public class Datumsrechner
	{

		public static DateTime Calculate(DateTime date, EDateDirection direction)
		{
			switch(direction)
			{
				case EDateDirection.RealToSim:
					return RealToSim(date);
				case EDateDirection.SimToReal:
					throw new NotImplementedException();
				default:
					throw new InvalidOperationException("Unknown Error");
			}
		}

		public static DateTime RealToSim(DateTime realDate)
		{
			if(realDate < new DateTime(2008, 10, 1))
				throw new ArgumentOutOfRangeException("realDate", realDate, "Calculation RL to SY only possible since 2008-10-01");
			else
				return RealToSimPost2020(realDate);
		}

		/// <summary>
		/// Rechnet das angegebene RL-Datum in ein SY-Datum um
		/// </summary>
		/// <param name="realDate">RL-Datum</param>
		/// <returns>SY-Datum</returns>
		private static DateTime RealToSimPost2020(DateTime realDate)
		{
			var syYear = GetSimocracyYear(realDate);
			
			var rlTotalDaysQuarter = GetTotalDaysInQuarter(realDate);
			var syTotalDaysInYear = GetDaysInYear(syYear);
			var syDaysPerRlDay = rlTotalDaysQuarter.TotalDays / syTotalDaysInYear;

			var rlDayInQuarter = GetDayInQuarter(realDate);
			var syDayInYear = (int) (syDaysPerRlDay * rlDayInQuarter.TotalDays);

			var syDate = new DateTime(syYear, 1, 1).AddDays(syDayInYear);

			return syDate;
		}

		#region Calculation Utilities
		
		/// <summary>
		/// Rechnet das angegebene Datum in ein Simocracy-Jahr um
		/// </summary>
		/// <param name="realDate">Datum</param>
		/// <returns>Simocracy-Jahr</returns>
		public static int GetSimocracyYear(DateTime realDate)
		{
			return (realDate.Year - 2009) * 4 + 2020 + GetQuarter(realDate);
		}

		/// <summary>
		/// Gibt die Anzahl der Tage im angegebenen Jahr zurück
		/// </summary>
		/// <param name="year">Jahr</param>
		/// <returns>Anzahl der Tage des Jahres</returns>
		public static int GetDaysInYear(int year)
		{
			var thisYear = new DateTime(year, 1, 1);
			var nextYear = new DateTime(year + 1, 1, 1);

			return (nextYear - thisYear).Days;
		}

		#endregion

		#region Quarter Utilities


		/// <summary>
		/// Gibt den Tag im Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Tag im Quartal</returns>
		private static TimeSpan GetDayInQuarter(DateTime date)
		{
			return GetFirstDateInQuarter(date) - date;
		}

		/// <summary>
		/// Gibt die Tage im Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Anzahl Tage im Quartal</returns>
		private static TimeSpan GetTotalDaysInQuarter(DateTime date)
		{
			return GetFirstDateInQuarter(date) - GetLastDateInQuarter(date);
		}

		/// <summary>
		/// Gibt den ersten Tag im Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Erster Tag im Quartal</returns>
		private static DateTime GetFirstDateInQuarter(DateTime date)
		{
			return new DateTime(date.Year, 3 * GetQuarter(date) - 2, 1);
		}

		/// <summary>
		/// Gibt den letzten Tag im Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Letzter Tag im Quartal</returns>
		private static DateTime GetLastDateInQuarter(DateTime date)
		{
			return new DateTime(date.Year, 3 * GetQuarter(date) + 1, 1).AddDays(-1);
		}

		/// <summary>
		/// Gibt das Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Quartal</returns>
		private static int GetQuarter(DateTime date)
		{
			return (int) Math.Ceiling(date.Month / 3d);
		}

		#endregion

	}
}
