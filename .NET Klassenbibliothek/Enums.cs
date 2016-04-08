using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Simocracy
{
	public enum EDateDirection
	{
		[Display(Description ="Realzeit nach Simocracy-Zeit")]
		RealToSim = 0,

		[Display(Description = "Simocracy-Zeit ach Realzeit")]
		SimToReal = 1
	}
}
