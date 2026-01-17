<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation" class="flex justify-center">
    <ul class="inline-flex h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden text-sm font-medium">
        
        <!-- Previous -->
        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" aria-label="First"
                   class="inline-flex h-full w-9 items-center justify-center border-r border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                    <i class="fas fa-angle-double-left text-xs"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPrevious() ?>" aria-label="Previous"
                   class="inline-flex h-full w-9 items-center justify-center border-r border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                    <i class="fas fa-angle-left text-xs"></i>
                </a>
            </li>
        <?php endif ?>

        <!-- Links -->
        <?php foreach ($pager->links() as $link) : ?>
            <li>
                <a href="<?= $link['uri'] ?>"
                   class="inline-flex h-full w-9 items-center justify-center border-r border-slate-100 dark:border-slate-700 transition-colors
                   <?= $link['active'] 
                       ? 'bg-indigo-50 text-indigo-600 font-bold dark:bg-slate-700 dark:text-white' 
                       : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white' 
                   ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <!-- Next -->
        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>" aria-label="Next"
                   class="inline-flex h-full w-9 items-center justify-center border-r border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                    <i class="fas fa-angle-right text-xs"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" aria-label="Last"
                   class="inline-flex h-full w-9 items-center justify-center bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                    <i class="fas fa-angle-double-right text-xs"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
