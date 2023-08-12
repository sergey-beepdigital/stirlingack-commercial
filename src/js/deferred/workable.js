import 'timeago';

var WorkableAPI = WorkableAPI || {};

(function ($) {
    WorkableAPI = function () {
        var workTypeOptions = [];
        var departmentOptions = [];
        var locationOptions = [];

        var jobsList = [];

        var sinceId = 0;
        var element = {
            list: '[data-workable="list"]',
            form: '[data-workable="filterForm"]'
        };

        var action = {
            loadMore: '[data-workable="loadMore"]'
        };

        return {
            init: function () {
                WorkableAPI.events();
                WorkableAPI.loadJobs();
            },

            events: function () {
                $(document)
                    .on('click', action.loadMore, WorkableAPI.loadMore)
                    .on('submit', element.form, WorkableAPI.filterList)
                ;
            },

            loadJobs: function () {
                $.ajax({
                    method: "POST",
                    url: crowdAjax,
                    beforeLoad: ajaxLoader(true),
                    data: {
                        action: 'jobs_list'
                    },
                    success: function (data) {
                        jobsList = data;

                        WorkableAPI.collectFilterValues();
                        WorkableAPI.render();

                        ajaxLoader(false);
                    }
                });
            },

            render: function () {
                WorkableAPI.renderFilters()
                WorkableAPI.renderList(jobsList)
            },

            collectFilterValues: function () {
                jobsList.forEach(function (item) {
                    workTypeOptions.push(item.employment_type);
                    departmentOptions.push(item.department_hierarchy[0].name);
                    locationOptions.push(item.location.city);
                    locationOptions.push(item.location.country);
                    locationOptions.push(item.location.region);
                });

                workTypeOptions = new Set(workTypeOptions);
                departmentOptions = new Set(departmentOptions);
                locationOptions = new Set(locationOptions);
            },

            renderFilters: function () {
                workTypeOptions.forEach(item => {
                    $('[name="work_type"]').append('<option>' + item + '</option>');
                })

                departmentOptions.forEach(item => {
                    $('[name="department"]').append('<option>' + item + '</option>');
                })

                locationOptions.forEach(item => {
                    $('[name="location"]').append('<option>' + item + '</option>');
                })
            },

            renderList: function (list) {
                console.log(list);
                var html = '';

                list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

                list.forEach(function (item) {
                    html += '<div class="workable-job-item">';
                    html += '<a href="' + location.href + '?shortcode=' + item.shortcode + '">View Position</a>';
                    html += '<div class="workable-job-item__title">';
                    html += '<h5 class="text-brand-red font-weight-bold">' + item.title + '</h5>';
                    html += '<div>Posted ' + $.timeago(item.created_at) + '</div>';
                    html += '</div>';
                    html += '<div class="workable-job-item__location">' + item.location.location_str + '</div>';
                    html += '<div class="workable-job-item__department">' + item.department_hierarchy[0].name + '</div>';
                    html += '<div class="workable-job-item__job-type text-center">';
                    html += item.employment_type;
                    if (item.location.workplace_type == 'remote') {
                        html += '<div><span class="badge badge-primary">Remote</span></div>';
                    }
                    html += '</div>';
                    html += '</div>';
                })

                $(element.list).html(html);
            },

            filterList: function (e) {
                e.preventDefault();

                var fromData = new FormData(e.target);
                var filters = {};
                var filteredList = [];

                var formTitle = fromData.get('job_title');
                var formLocation = fromData.get('location');
                var formDepartment = fromData.get('department');
                var formWorkType = fromData.get('work_type');
                var formRemote = fromData.get('remote_only');

                if (formTitle) filters.title = item => item.title.toLowerCase().indexOf(formTitle.toLowerCase()) !== -1;
                if (formLocation) filters.location = item => item.location.city == formLocation || item.location.country == formLocation || item.location.region == formLocation;
                if (formDepartment) filters.department = item => item.department_hierarchy[0].name == formDepartment;
                if (formWorkType) filters.employment_type = item => item.employment_type === formWorkType;
                if (formRemote) filters.remote = item => item.location.workplace_type == "remote";

                filteredList = jobsList.filter(item => {
                        return Object.keys(filters).every(key => {
                            if (typeof filters[key] !== 'function') return true;
                            return filters[key](item);
                        });
                    }
                );

                $(element.list).html('');
                WorkableAPI.renderList(filteredList);
            },

            _loadJobs: function () {
                var $list = $(element.list);

                if ($list.length) {
                    //ajaxLoader(true);
                    $.ajax({
                        method: "POST",
                        url: crowdAjax,
                        //beforeLoad: ,
                        data: {
                            action: 'jobs_list',
                            since_id: sinceId
                        },
                        success: function (response) {
                            $list.append(response.html);

                            if (response.since_id) {
                                $(action.loadMore).attr('data-since-id', response.since_id);
                                $(action.loadMore).parent().show();
                            } else {
                                sinceId = 0;
                                $(action.loadMore).parent().hide();
                            }

                            //ajaxLoader(false);
                        }
                    });
                }
            },

            loadMore: function () {
                var $this = $(this);
                var data = $this.data();

                console.log(data);

                if (data.sinceId !== undefined) {
                    sinceId = data.sinceId;
                    WorkableAPI.loadJobs();
                }

                return false;
            }
        }
    }();

    $(document).ready(WorkableAPI.init);
})(jQuery);
