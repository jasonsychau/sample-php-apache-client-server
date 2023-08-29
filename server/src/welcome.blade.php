<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
	<style>
	  .hidden {
	    display: none;
	  }
	  label {
	    margin-bottom: 10px;
	    display: block;
	  }
	</style>
	<meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('data'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
	    @else
		<div>Welcome</div>
		<!-- <pre id="ipt-user-profile"></pre> -->
		<button id="btn-login" disabled="true" onclick="login()">Log in</button>
		<button id="btn-logout" disabled="true" onclick="logout()">Log out</button>
		<div style="width:100%;display:flex;flex-direction:column;">
			<span id='message'></span>
			<table>
				<thead>
					<tr><th>30 Day Data Tracker</th></tr>
					<tr>
						<th>Revenue</th>
						<th>New Followers Count</th>
						<th>Top Selling Items</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td id="revenue"></td>
						<td id="new-followers"></td>
						<td id="top-items"></td>
					</tr>
				</tbody>
			</table>
			<table id='notifications-table'>
			</table>
		</div>
            @endif
	</div>
	<script src="https://cdn.auth0.com/js/auth0-spa-js/2.0/auth0-spa-js.production.js"></script>	
	<script >
	  const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;
	  let auth0Client = null;
  	  const fetchAuthConfig = () => fetch("/auth_config.json");
	  const configureClient = async () => {
  	    const response = await fetchAuthConfig();
	    const config = await response.json();

  	    auth0Client = await auth0.createAuth0Client({
	      domain: config.domain,
	      clientId: config.clientId
  	    });
	  };	  
	  window.onload = async () => {
	    await configureClient();
	    updateUI();

	    const isAuthenticated = await auth0Client.isAuthenticated();
	    if (isAuthenticated) {

	    }
	    const query = window.location.search;
	    if (query.includes("code=") && query.includes("state=")) {
		await auth0Client.handleRedirectCallback();
		updateUI();
		window.history.replaceState({}, document.title, "/");
	    }
	  }
	  const updateUI = async () => {
	    const isAuthenticated = await auth0Client.isAuthenticated();
	    document.getElementById("btn-logout").disabled = !isAuthenticated;
	    document.getElementById("btn-login").disabled = isAuthenticated;
	    if (isAuthenticated) {
		    // document.getElementById("ipt-user-profile").textContent = JSON.stringify(await auth0Client.getUser());
		    userData = await auth0Client.getUser();
			let reqData = new FormData();
	        reqData.append("email", userData['email']);
			fetch('http://localhost:8000/getdata', {
			method:'post',
				headers:{'X-CSRF-Token': csrfToken},
				credentials:'same-origin',
				body:reqData
			}).then(data=>{return data.json()})
			.then(res=>{
				if (res.length == 0) {
					document.getElementById("message").innerText = "No data to display!";
				} else {
					if ('notifications' in res) {
						let table = document.getElementById('notifications-table');
						let header = document.createElement('thead');
						let headerRow = document.createElement('tr');
						headerRow.innerText = "Nofitications";
						header.appendChild(headerRow);
						table.appendChild(header);
						res['notifications'].forEach(datum=>{
							let row = document.createElement('tr');
							let cell = document.createElement('td');
							cell.innerText = datum.message;
							row.appendChild(cell);
							table.appendChild(row);
						});
					} else {
						document.getElementById('notifications-table').innerHTML = "No data here";
					}
					if ('revenue' in res) {
						let revenueString = "";
						if ('subs' in res['revenue']) {
							let subsAmount = res['revenue']['subs']
							revenueString = "$" + ((subsAmount.length == 0) ? "0" : subsAmount) + " from subscriptions. "
						}
						if ('donationsAndPurchases' in res['revenue']) {
							let donationsAndPurchasesAmount = Object.keys(res['revenue']['donationsAndPurchases']).map(key=> {
								return res['revenue']['donationsAndPurchases'][key] + " " + key;
							}).join(" and ");
							revenueString = revenueString + ((donationsAndPurchasesAmount.length == 0) ? "$0" : donationsAndPurchasesAmount) + " from donations and merchandising.";
						}
						if (revenueString.length == 0) revenueString = "No data here";
						document.getElementById("revenue").innerText = revenueString;
					}
					if ('topItems' in res) {
						document.getElementById('top-items').innerText = res['topItems'].map(item => item.item_name).join(", ");
					} else {
						document.getElementById('top-items').innerText = "No data here";
					}
					if ('newFollowers' in res) {
						document.getElementById('new-followers').innerText = res['new-followers'];
					} else {
						document.getElementById('new-followers').innerText = "No data here";
					}
				}
			}).catch(err=>{
				console.error(err.toString());
				document.getElementById('message').innerHTML = err.toString();
			});
	    }
	  }
	  const login = async () => {
  	    await auth0Client.loginWithRedirect({
    	      authorizationParams: {
      	        redirect_uri: window.location.origin
    	      }
  	    });
	  };
	  const logout = () => {
  	    auth0Client.logout({
    	      logoutParams: {
                returnTo: window.location.origin
    	      }
  	    });
	  };
	</script>
    </body>
</html>
