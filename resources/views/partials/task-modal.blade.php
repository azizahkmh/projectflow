<x-modal name="edit-task" title="Edit task" description="Update the details of this task." max-width="lg">
    <form method="POST"
          x-data="{ task: null }"
          x-on:task-edit.window="task = $event.detail; $store.modals.show('edit-task')"
          :action="'/tasks/' + (task?.id ?? '')"
          class="space-y-4 px-6 py-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="label">Title</label>
            <input name="title" type="text" required :value="task?.title" class="input">
        </div>

        <div>
            <label class="label">Description</label>
            <textarea name="description" rows="2" :value="task?.description" class="input resize-none"></textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Project</label>
                <select name="project_id" required class="input">
                    @foreach ($projects ?? [] as $project)
                        <option value="{{ $project->id }}" :selected="String(task?.project_id) === '{{ $project->id }}'">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Assignee</label>
                <select name="assignee_id" class="input">
                    <option value="">Unassigned</option>
                    @foreach ($assignees ?? [] as $assignee)
                        <option value="{{ $assignee->id }}" :selected="String(task?.assignee_id) === '{{ $assignee->id }}'">{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label">Status</label>
                <select name="status" class="input">
                    @foreach (['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'In Review', 'done' => 'Done'] as $value => $label)
                        <option value="{{ $value }}" :selected="task?.status === '{{ $value }}'">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Priority</label>
                <select name="priority" class="input">
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" :selected="task?.priority === '{{ $value }}'">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Due date</label>
                <input name="due_date" type="date" :value="task?.due_date?.substring(0,10)" class="input">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="$store.modals.close('edit-task')" class="btn-ghost">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="size-4" /> Save changes
            </button>
        </div>
    </form>
</x-modal>
{{-- Shared "New task" modal. --}}
<x-modal name="new-task" title="New task" description="Add work to a project and assign an owner." max-width="lg">
    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4 px-6 py-5">
        @csrf

        <div>
            <label for="nt-title" class="label">Task title</label>
            <input id="nt-title" name="title" type="text" required value="{{ old('title') }}"
                   placeholder="e.g. Design the homepage" class="input @error('title') input-error @enderror">
            @error('title')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="nt-description" class="label">Description <span class="font-normal text-muted">(optional)</span></label>
            <textarea id="nt-description" name="description" rows="2" placeholder="Add context or acceptance criteria…"
                      class="input resize-none">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="nt-project" class="label">Project</label>
                <select id="nt-project" name="project_id" required class="input @error('project_id') input-error @enderror">
                    <option value="">Select project…</option>
                    @foreach ($projects ?? [] as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nt-assignee" class="label">Assignee</label>
                <select id="nt-assignee" name="assignee_id" class="input">
                    <option value="">Unassigned</option>
                    @foreach ($assignees ?? [] as $assignee)
                        <option value="{{ $assignee->id }}" @selected(old('assignee_id') == $assignee->id)>{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="nt-status" class="label">Status</label>
                <select id="nt-status" name="status" class="input">
                    @foreach (['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'In Review', 'done' => 'Done'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'todo') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="nt-priority" class="label">Priority</label>
                <select id="nt-priority" name="priority" class="input">
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="nt-due" class="label">Due date</label>
                <input id="nt-due" name="due_date" type="date" value="{{ old('due_date') }}" class="input">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="$store.modals.close('new-task')" class="btn-ghost">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="size-4" /> Create task
            </button>
        </div>
    </form>
</x-modal>
