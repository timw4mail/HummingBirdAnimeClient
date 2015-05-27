	<nav>
		<ul>
			<li class="<?= is_selected('/', $route_path) ?>"><a href="/">Watching</a></li>
			<li class="<?= is_selected('/plan_to_watch', $route_path) ?>"><a href="/plan_to_watch">Plan to Watch</a></li>
			<li class="<?= is_selected('/on_hold', $route_path) ?>"><a href="/on_hold">On Hold</a></li>
			<li class="<?= is_selected('/dropped', $route_path) ?>"><a href="/dropped">Dropped</a></li>
			<li class="<?= is_selected('/completed', $route_path) ?>"><a href="/completed">Completed</a></li>
			<li class="<?= is_selected('/all', $route_path) ?>"><a href="/all">All</a></li>
		</ul>
	</nav>