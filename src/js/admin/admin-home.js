new Chart($("#new-customers")[0].getContext("2d"), {
	type: "line",
	data: {
		labels: $("#new-customers").data("months").split(","),
		datasets: [
			{
				label: " Users",
				data: $("#new-customers").data("customers").split(","),
				borderColor: "#65b32e",
				borderWidth: 3,
				tension: 0.35
			}
		]
	},
	options: {
		scales: {
			y: {
				type: "linear",
				beginAtZero: true,
				title: {
					display: true,
					text: "Users",
					color: "black"
				},
				ticks: {
					color: "black"
				},
				grid: {
					color: "#cccccc"
				}
			},
			x: {
				ticks: {
					color: "black"
				},
				grid: {
					color: "#cccccc"
				}
			}
		},
		plugins: {
			legend: {
				display: false
			}
		}
	}
})

new Chart($("#login-devices")[0].getContext("2d"), {
	type: "pie",
	data: {
		labels: $("#login-devices").data("devices").split(","),
		datasets: [
			{
				label: " Login Count",
				data: $("#login-devices").data("devices-count").split(","),
				backgroundColor: ["#228B22", "#009E60", "#50C878"]
			}
		]
	},
	options: {
		plugins: {
			legend: {
				labels: {
					color: "black"
				}
			}
		}
	}
})

new Chart($("#order-frequency")[0].getContext("2d"), {
	type: "line",
	data: {
		labels: $("#order-frequency").data("months").split(","),
		datasets: [
			{
				label: " Orders",
				data: $("#order-frequency").data("orders").split(","),
				borderColor: "#65b32e",
				borderWidth: 3,
				tension: 0.35
			}
		]
	},
	options: {
		scales: {
			y: {
				type: "linear",
				beginAtZero: true,
				title: {
					display: true,
					text: "Orders",
					color: "black"
				},
				ticks: {
					color: "black"
				},
				grid: {
					color: "#cccccc"
				}
			},
			x: {
				ticks: {
					color: "black"
				},
				grid: {
					color: "#cccccc"
				}
			}
		},
		plugins: {
			legend: {
				display: false
			}
		}
	}
})

new Chart($("#order-status")[0].getContext("2d"), {
	type: "pie",
	data: {
		labels: $("#order-status").data("statuses").split(","),
		datasets: [
			{
				label: " Orders",
				data: $("#order-status").data("statuses-count").split(","),
				backgroundColor: ["#228B22", "#009E60", "#50C878"]
			}
		]
	},
	options: {
		plugins: {
			legend: {
				labels: {
					color: "black"
				}
			}
		}
	}
})

// const salesCategoryPieCtx = document.getElementById("salesCategoryPieChart").getContext("2d")
// const salesCategoryPieChart = new Chart(salesCategoryPieCtx, {
//     type: "pie",
//     data: {
//         labels: ["Category A", "b", "C"],
//         datasets: [
//             {
//                 label: "RM",
//                 data: [60, 20, 20],
//                 backgroundColor: ["#228B22", "#009E60", "#50C878"]
//             }
//         ]
//     },
//     options: {
//         responsive: true
//     }
// })

// const salesRegionPieCtx = document.getElementById("salesRegionPieChart").getContext("2d")
// const salesRegionPieChart = new Chart(salesRegionPieCtx, {
//     type: "pie",
//     data: {
//         labels: [
//             "Perlis",
//             "Labuan",
//             "PutraJaya",
//             "Kuala Lumpur",
//             "Kedah",
//             "Kelantan",
//             "Melaka",
//             "Negeri Sembilan",
//             "Pahang",
//             "Perak",
//             "Pulau Pinang",
//             "Sabah",
//             "Sarawak",
//             "Selangor",
//             "Terengganu",
//             "Johor"
//         ],
//         datasets: [
//             {
//                 label: "RM",
//                 data: [60, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 10, 10, 10, 10],
//                 backgroundColor: [
//                     "#00FA9A",
//                     "#228B22",
//                     "#ADFF2F",
//                     "#3CB371",
//                     "#7FFF00",
//                     "#009E60",
//                     "#9ACD32",
//                     "#32CD32",
//                     "#8FBC8F",
//                     "#7CFC00",
//                     "#20B2AA",
//                     "#2E8B57",
//                     "#66CDAA",
//                     "#98FB98",
//                     "#9FFEBF",
//                     "#50C878"
//                 ]
//             }
//         ]
//     },
//     options: {
//         responsive: true
//     }
// })
