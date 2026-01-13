/**
 *
 */
const announcement = {
	userId: null,
	product: null,
	ses: null,
	cname: null,
	cookieName: null,
	coockAge: 3, // em dias,

	init: (userId, product, ses, cname, infinity = false) => {
		if(infinity) {
			announcement.coockAge = (365) // 1 ano (máximo permitipo por max-age)
		}
		announcement.userId = userId
		announcement.product = product
		announcement.cname = cname
		announcement.ses = ses
		announcement.cookieName = `${announcement.cname}_${announcement.userId}`
	},

	start: (product) => {
		if (announcement.product !== product) {
			return false // Retorna false se o produto não corresponde
		}

		const cookieValue = announcement.getCookie()

		if (!cookieValue) {
			announcement.setCookie(announcement.userId, announcement.ses, null)
			return true
		}

		const cookieData = JSON.parse(atob(cookieValue))

		if (cookieData.timestamp === null) {
			if (cookieData.ses !== announcement.ses) {
				announcement.setCookie(announcement.userId, announcement.ses, null)
				return true
			}
		} else if (announcement.isTimestampExpired(cookieData.timestamp)) {
			announcement.clearTimestamp()
			return true
		}

		return false
	},

	setCookie: (userId, ses, timestamp = null) => {
		const data = {
			userId: userId,
			ses: ses,
			timestamp: timestamp,
		}

		const cookieValue = btoa(JSON.stringify(data));
		document.cookie = `${announcement.cookieName}=${cookieValue}; path=/; max-age=${announcement.getTimeExpires()};`;
	},

	getCookie: () => {
		const value = `; ${document.cookie}`
		const parts = value.split(`; ${announcement.cookieName}=`)
		if (parts.length === 2) return parts.pop().split(";").shift()
		return null
	},

	updateTimestamp: () => {
		const cookieValue = announcement.getCookie()
		if (cookieValue) {
			const cookieData = JSON.parse(atob(cookieValue))
			cookieData.timestamp = new Date().toISOString() // Atualiza o timestamp para a data/hora atual
			document.cookie = `${announcement.cookieName}=${btoa(JSON.stringify(cookieData))}; path=/; max-age=${announcement.getTimeExpires()};`
		}
	},

	clearTimestamp: () => {
		const cookieValue = announcement.getCookie()
		if (cookieValue) {
			const cookieData = JSON.parse(atob(cookieValue))
			cookieData.timestamp = null;
			document.cookie = `${announcement.cookieName}=${btoa(JSON.stringify(cookieData))}; path=/; max-age=${announcement.getTimeExpires()};`
		}
	},

	// Função para verificar se o timestamp expirou
	isTimestampExpired: (timestamp) => {
		const savedTimestamp = new Date(timestamp)
		const currentTimestamp = new Date()
		const hoursDiff = (currentTimestamp - savedTimestamp) / (1000 * 60 * 60)
		return hoursDiff > announcement.coockAge
	},

	getTimeExpires: () => {
		return announcement.coockAge * 24 * 60 * 60;
	}
}
