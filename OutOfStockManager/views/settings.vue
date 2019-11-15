<template>
  <div class="main-content">
    <div class="header bg-gradient-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Sellfino App Store</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark m-0 p-0 text-sm font-weight-600 nobg">
                  <li class="breadcrumb-item"><a href="" class="text-neutral" @click.prevent="$root.view = 'apps'"><i class="fas fa-home"></i></a></li>
                  <li class="breadcrumb-item"><a href="" class="text-neutral" @click.prevent="$root.view = 'OutOfStockManager-index'">Out of Stock Manager</a></li>
                  <li class="breadcrumb-item active text-light">Settings</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <div class="loading text-sm font-weight-600 text-neutral" v-if="loading">Loading... <span class="badge ml-2"></span></div>
              <a href="" class="btn btn-sm btn-neutral" v-if="!loading" :class="{ disabled: saving }" @click.prevent="save">Save</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--6" v-if="!loading">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Email Settings <p class="m-0"><small>Setup email template that will be used for notifications.</small></p></h3>
            </div>
            <div class="card-body pt-0">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="settings-subject" class="form-control-label">Subject</label>
                    <input type="text" id="settings-subject" class="form-control" v-model="settings.subject">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="settings-body" class="form-control-label">Body</label>
                    <textarea id="settings-body" class="form-control" v-model="settings.body" rows=5></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: true,
      saving: false,
      settings: {
        subject: '',
        body: ''
      }
   }
  },
  methods: {
    save: function() {
      var self = this
      this.saving = true

      url = '/app/OutOfStockManager/settings/update'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify(this.settings)
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.saving = false
        self.$root.showToast('Settings saved')
      })
      .catch((error) => {
        alert(error)
        self.saving = false
      })
    }
  },
  mounted: function() {
    var self = this

    url = '/app/OutOfStockManager/settings'
    params = {
      method: 'GET',
      headers: this.$root.fetchHeaders
    }

    fetch(url, params)
    .then(errorCheck)
    .then(function(res) {
      if (res) {
        Vue.set(self, 'settings', Object.assign({},res))
      }
      self.loading = false
    })
    .catch((error) => {
      alert(error)
      self.loading = false
    })
  }
}
</script>