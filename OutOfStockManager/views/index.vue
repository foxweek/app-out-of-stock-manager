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
                  <li class="breadcrumb-item active text-light">Out of Stock Manager</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <div class="loading text-sm font-weight-600 text-neutral" v-if="loading">Loading... <span class="badge ml-2"></span></div>
              <a href="#" class="btn btn-sm btn-neutral" @click.prevent="$root.view = 'OutOfStockManager-settings'">Settings</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--6">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Alerts Awaiting</h3>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" v-if="list.length">
                <thead class="thead-light">
                  <tr>
                    <th class="w-30">Product</th>
                    <th class="w-30">Variant</th>
                    <th class="w-20">Total Saved Emails</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="list">
                  <tr v-for="(el, index) in list">
                    <th scope="row">
                      <div class="media align-items-center">
                        <a class="avatar rounded-circle mr-3" v-if="el.image" @click.prevent="openModal(el)">
                          <img :src="img_url(el.image, 'small')">
                        </a>
                        <div class="media-body">
                          <span class="name mb-0 text-sm">
                            <a href="" class="text-primary" @click.prevent="openModal(el)">{{ el.product.title }}</a>
                          </span>
                        </div>
                      </div>
                    </th>
                    <td>
                      {{ el.variant.title || '-' }}
                    </td>
                    <td>
                      {{ el.emails.length }}
                    </td>
                    <td class="text-right">
                      <a :href="admin_link(el.product.id, 'products')" class="btn btn-icon-only text-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
              <table class="table align-items-center table-flush" v-else>
                <thead class="thead-light">
                  <tr>
                    <th>
                      <span v-if="loading">Loading... Please wait</span>
                      <span v-else>There are no active alerts</span>
                    </th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
          <div class="card-footer py-4 nobg">
            <nav>
              <ul class="pagination justify-content-center mb-0">
                <li class="page-item" :class="{ disabled: page.prev == null || loading }">
                  <a class="page-link" href="#" @click.prevent="prevPage">
                    <i class="fas fa-angle-left"></i>
                    <span class="sr-only">Previous</span>
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: page.next == null || loading }">
                  <a class="page-link" href="#" @click.prevent="nextPage">
                    <i class="fas fa-angle-right"></i>
                    <span class="sr-only">Next</span>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <component is="inc-OutOfStockManager-modal" :title="modal.title" :emails="modal.emails" />

  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: true,
      list: [],
      page: 1,
      search: '',
      modal: false
   }
  },
  methods: {
    load: function() {
      var self = this

      url = '/app/OutOfStockManager/products?page=' + this.page + '&search=' + this.search
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        if (!res) {
          self.list = []
        } else {
          self.list = Object.keys(res).map(function (key) { return res[key] })
        }
        self.loading = false
      })
      .catch((error) => {
        alert(error)
      })
    },
    openModal: function(el) {
      this.modal = {
        title: el.product.title + ' ' + (el.variant.title ? ' - ' + el.variant.title : ''),
        emails: el.emails
      }
    },
    nextPage: function() {
      this.page++
      this.load()
    },
    prevPage: function() {
      this.page--
      this.load()
    }
  },
  mounted: function() {
    this.load()
  }
}
</script>