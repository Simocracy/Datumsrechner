using System;
using System.ComponentModel.DataAnnotations;

namespace Simocracy
{
	/// <summary>
	/// Datumsrechner für Simocracy
	/// </summary>
	/// <remarks>
	/// Optimiert und Übersetzt vom Simocracy PostWriter 2.0.5
	/// Basierend auf dem alten PHP-Datumsrechner von Fluggi
	/// </remarks>
	public static class Datumsrechner
	{

		#region Calculation Methods

		/// <summary>
		/// Rechnet zwischen Realzeit und Simocracy-Zeit um.
		/// </summary>
		/// <param name="date">Ausgangsdatum</param>
		/// <param name="direction">Berechnungsrichtung</param>
		/// <returns>Umgerechnetes Datum</returns>
		public static DateTime Calculate(DateTime date, EDateDirection direction)
		{
			switch(direction)
			{
				case EDateDirection.RealToSim:
					return RealToSim(date);
				case EDateDirection.SimToReal:
					return SimToReal(date);
				default:
					throw new InvalidOperationException("Unknown Error");
			}
		}

		/// <summary>
		/// Rechnet das angegebene RL-Datum in ein SY-Datum um
		/// </summary>
		/// <param name="realDate">RL-Datum</param>
		/// <returns>SY-Datum</returns>
		public static DateTime RealToSim(DateTime realDate)
		{
			if(realDate < new DateTime(2008, 10, 1))
				throw new ArgumentOutOfRangeException("realDate", realDate, "Calculation RL to SY only possible after 2008-10-01");
			else
				return RealToSimPost2020(realDate);
		}

		/// <summary>
		/// Rechnet das angegebene RL-Datum in ein SY-Datum ab 2020 um
		/// </summary>
		/// <param name="realDate">RL-Datum</param>
		/// <returns>SY-Datum</returns>
		private static DateTime RealToSimPost2020(DateTime realDate)
		{
			if(realDate < new DateTime(2008, 10, 1))
				throw new ArgumentOutOfRangeException("realDate", realDate, "Calculation RL to SY only possible after 2008-10-01");

			// SY-Jahr berechnen
			var syYear = GetSimocracyYear(realDate);

			// Tage im RL-Quartal
			var rlTotalDaysQuarter = GetTotalDaysInQuarter(realDate);

			// Tage im SY-Jahr
			var syTotalDaysInYear = GetDaysInYear(syYear);

			// SY-Tage pro RL-Tag
			var syDaysPerRlDay = rlTotalDaysQuarter.TotalDays / syTotalDaysInYear;

			// Vergangene RL-Tage im Quartal
			var rlDayInQuarter = GetDayInQuarter(realDate).TotalDays - 1;

			// SY-Tag im Jahr
			var syDayInYear = (rlDayInQuarter / syDaysPerRlDay);

			// SY-Datum ermitteln
			var syDate = new DateTime(syYear, 1, 1).AddDays(syDayInYear);

			return syDate;
		}

		/// <summary>
		/// Rechnet das angegebene SY-Datum ab 2020 in ein RL-Datum um
		/// </summary>
		/// <param name="simDate">SY-Datum</param>
		/// <returns>RL-Datum</returns>
		public static DateTime SimToReal(DateTime simDate)
		{
			if(simDate < new DateTime(2020, 1, 1))
				throw new ArgumentOutOfRangeException("simDate", simDate, "Calculation SY to RL only possible after 2020-01-01");
			else
				return SimToRealPost2020(simDate);
		}

		/// <summary>
		/// Rechnet das angegebene SY-Datum ab 2020 in ein RL-Datum um
		/// </summary>
		/// <param name="simDate">SY-Datum</param>
		/// <returns>RL-Datum</returns>
		private static DateTime SimToRealPost2020(DateTime simDate)
		{
			if(simDate < new DateTime(2020, 1, 1))
				throw new ArgumentOutOfRangeException("simDate", simDate, "Calculation SY to RL only possible after 2020-01-01");

			// RL-Jahr berechnen
			var rlYear = GetRealYear(simDate);

			// RL-Quartal berechnen
			var rlQuarter = simDate.Year % 4;
			if(rlQuarter == 0)
				rlQuarter = 4;

			// Vergangene Tage im SY-Jahr
			var syDayInYear = simDate.TimeOfDay.Add(TimeSpan.FromDays(simDate.DayOfYear)).TotalDays - 1;

			// Tage im RL-Quartal
			var rlTotalDaysQuarter = GetTotalDaysInQuarter(rlYear, rlQuarter);

			// Tage im SY-Jahr
			var syTotalDaysInYear = GetDaysInYear(simDate.Year);

			// SY-Tage pro RL-Tag
			var syDaysPerRlDay = rlTotalDaysQuarter.TotalDays / syTotalDaysInYear;

			// Vergangene Tage im RL-Quartal
			var rlDayInQuarter = syDayInYear * syDaysPerRlDay;

			// RL-Datum ermitteln
			var date = new DateTime(rlYear, (rlQuarter * 3) - 2, 1).AddDays(rlDayInQuarter);

			return date;
		}

		#endregion

		#region Calculation Utilities

		/// <summary>
		/// Rechnet das angegebene RL-Datum in ein Simocracy-Jahr um
		/// </summary>
		/// <param name="realDate">Datum</param>
		/// <returns>Simocracy-Jahr</returns>
		public static int GetSimocracyYear(DateTime realDate)
		{
			return (realDate.Year - 2009) * 4 + 2020 + GetQuarter(realDate);
		}

		/// <summary>
		/// Rechnet das angegebene Simocracy-Datum in ein RL-Jahr um
		/// </summary>
		/// <param name="simDate">Datum</param>
		/// <returns>RL-Jahr</returns>
		public static int GetRealYear(DateTime simDate)
		{
			return 2008 + (int) Math.Ceiling((simDate.Year - 2020) / 4D);
		}

		/// <summary>
		/// Gibt die Anzahl der Tage im angegebenen Jahr zurück
		/// </summary>
		/// <param name="year">Jahr</param>
		/// <returns>Anzahl der Tage des Jahres</returns>
		private static int GetDaysInYear(int year)
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
			return date - GetFirstDateInQuarter(date) + TimeSpan.FromDays(1);
		}

		/// <summary>
		/// Gibt die Tage im angegebenen Quartal des angegebenen Jahres zurück
		/// </summary>
		/// <param name="year">Jahr</param>
		/// <param name="quarter">Quartal</param>
		/// <returns>Anzahl Tage im Quartal</returns>
		private static TimeSpan GetTotalDaysInQuarter(int year, int quarter)
		{
			var date = new DateTime(year, quarter * 3, 1);
			return GetTotalDaysInQuarter(date);
		}

		/// <summary>
		/// Gibt die Tage im Quartal des angegebenen Datums zurück
		/// </summary>
		/// <param name="date">Datum</param>
		/// <returns>Anzahl Tage im Quartal</returns>
		private static TimeSpan GetTotalDaysInQuarter(DateTime date)
		{
			return GetLastDateInQuarter(date) - GetFirstDateInQuarter(date) + TimeSpan.FromDays(1);
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
			var endMonth = GetQuarter(date) * 3;
			return new DateTime(date.Year, endMonth, DateTime.DaysInMonth(date.Year, endMonth));
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

	public enum EDateDirection
	{
		[Display(Description = "Realzeit nach Simocracy-Zeit")]
		RealToSim = 0,
		
		[Display(Description = "Simocracy-Zeit ach Realzeit")]
		SimToReal = 1
	}
}
