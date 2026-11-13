// Header switcher: picking a role selects the first person of that role, then reloads as them.
export default (options, role, person) => ({
    options,
    role,
    person,
    pickRole(form) {
        this.person = this.options[this.role][0];
        this.$nextTick(() => form.submit());
    },
});
