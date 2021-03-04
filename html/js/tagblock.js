$(document).ready(function() {
	var decodeHtmlEntity = function(str) {
		return str.replace(/&#(\d+);/g, function(match, dec) {
			return String.fromCharCode(dec);
		});
	};

	var cookie = unescape(Cookies.get('wf_tagblacklist'));
    cookie = cookie.replace(/\+/g, ' ');
    cookie = cookie.replace(/"/g,"");
	blacklistArray = cookie.split('|');
	var posts = document.querySelectorAll( '.post-card:not(.blocked)' );
	Array.prototype.forEach.call(posts, function(post) {
        postTags = JSON.parse(post.getAttribute('data-tags'));

		if (blacklistArray.length != 0) {
			if (blacklistArray.some(r=> postTags.includes(r))) {
				try {
					post.getElementsByClassName('card-header')[0].classList.add('tag-warning');
					post.classList.add('blocked');

					postHeader = post.getElementsByClassName('card-header')[0].outerHTML;
					randID = Math.random().toString(36).substring(7);
					postContent = post.getElementsByClassName('card-header')[0].remove();
					tags = blacklistArray.filter(value => postTags.includes(value));
					tagString = tags.join(', ');
					post.innerHTML = postHeader + '<button class="btn btn-light btn-sm btn-block tagblockbutton" type=button" data-toggle="collapse" data-target="#collapsedPost' + randID + '" aria-expanded="false" aria-controls='+ randID +'"><p>This post was automatically collapsed because these tags are on your blacklist:<br>'+ tagString +'<p>Click here to view it.</p><p><small>Full tags for this post: '+ postTags.join(', ') +'</small></button><div class="collapse" id="collapsedPost' + randID + '">'+ post.outerHTML +'</div>';

				}
				catch(err) {
					console.log("Hung up here: " + cookie + ',' + blacklistArray.length);
				}
			}
		}
    });

	var otherPosts = document.querySelectorAll( '.post-card:not(.blocked)' );
	Array.prototype.forEach.call(otherPosts, function(post) {
        postTags = JSON.parse(post.getAttribute('data-source-tags'));

		if (blacklistArray.length != 0) {
			if (blacklistArray.some(r=> postTags.includes(r))) {
				try {
                    console.log('block an OP tag')
					post.getElementsByClassName('card-header')[0].classList.add('tag-warning');
					post.classList.add('blocked');

					postHeader = post.getElementsByClassName('card-header')[0].outerHTML;
					randID = Math.random().toString(36).substring(7);
					postContent = post.getElementsByClassName('card-header')[0].remove();
					tags = blacklistArray.filter(value => postTags.includes(value));
					tagString = tags.join(', ');
					post.innerHTML = postHeader + '<button class="btn btn-light btn-sm btn-block tagblockbutton" type=button" data-toggle="collapse" data-target="#collapsedPost' + randID + '" aria-expanded="false" aria-controls='+ randID +'"><p>This post was automatically collapsed because the OP used tags that are on your blacklist:<br>'+ tagString +'<p>Click here to view it.</p><p><small>OP\'s tags for this post: '+ postTags.join(', ') +'</small></button><div class="collapse" id="collapsedPost' + randID + '">'+ post.outerHTML +'</div>';

				}
				catch(err) {
					console.log("Hung up here: " + cookie + ',' + blacklistArray.length);
				}
			}
		}
    });
});
